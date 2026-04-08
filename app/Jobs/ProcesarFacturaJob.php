<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use App\Services\SunatService;
use App\Services\GuardarComprobantes;
use App\Models\VentasModel\Venta;
use App\Models\ProductosModel\Producto;
use Greenter\Report\XmlUtils;
use Greenter\Model\Response\BillResult;

class ProcesarFacturaJob implements ShouldQueue
{
    use Queueable;

    protected $ventaId;

    public $tries = 5;
    public $backoff = [10, 30, 60];

    public function __construct($ventaId)
    {
        $this->ventaId = $ventaId;
    }

    public function handle(): void
    {
        $venta = Venta::with('detalles')->findOrFail($this->ventaId);

        // 🔥 Evitar reprocesar
        if ($venta->sunat_enviado) {
            return;
        }

        // 🔥 Marcar como procesando
        $venta->update([
            'estado_envio' => 'procesando'
        ]);

        try {

            /*
            |-----------------------------------------
            | MAPEAR DATA DESDE BD
            |-----------------------------------------
            */
            $data = $this->mapearVenta($venta);

            /*
            |-----------------------------------------
            | SUNAT
            |-----------------------------------------
            */
            $sunatService = new SunatService();

            $see = $sunatService->getSee();
            $invoice = $sunatService->getInvoice($data);

            $result = $see->send($invoice);
            /** @var BillResult $result */

            $xml = $see->getFactory()->getLastXml();
            $cdrZip = $result->isSuccess() ? $result->getCdrZip() : null;

            /*
            |-----------------------------------------
            | ARCHIVOS
            |-----------------------------------------
            */
            $archivos = new GuardarComprobantes();

            $rutaXml = $archivos->guardarXml($invoice, $xml);
            $rutaCdr = $cdrZip ? $archivos->guardarCdr($invoice, $cdrZip) : null;

            /*
            |-----------------------------------------
            | HASH
            |-----------------------------------------
            */
            $hash = (new XmlUtils())->getHashSign($xml);

            /*
            |-----------------------------------------
            | RESPUESTA SUNAT
            |-----------------------------------------
            */
            $sunatResponse = $sunatService->sunatResponse($result);

            /*
            |-----------------------------------------
            | STOCK (solo si aceptado)
            |-----------------------------------------
            */
            if ($sunatResponse['success']) {
                foreach ($venta->detalles as $item) {
                    Producto::where('codigo', $item->codigo_producto)
                        ->decrement('stock', $item->cantidad);
                }
            }

            /*
            |-----------------------------------------
            | PDF (solo si aceptado)
            |-----------------------------------------
            */
            $rutaPdf = null;

            if ($sunatResponse['success']) {
                $rutaPdf = $archivos->generarPdf($invoice);
            }

            /*
            |-----------------------------------------
            | ACTUALIZAR VENTA
            |-----------------------------------------
            */
            $venta->update([

                'sunat_enviado' => true,
                'fecha_envio_sunat' => now(),

                'estado_envio' => $sunatResponse['success']
                    ? 'aceptado'
                    : 'rechazado',

                'codigo_respuesta_sunat'
                    => $sunatResponse['cdrRespuesta']['code'] ?? null,

                'descripcion_respuesta_sunat'
                    => $sunatResponse['cdrRespuesta']['description'] ?? null,

                'hash_cpe' => $hash,

                'archivo_xml' => $rutaXml,
                'archivo_pdf' => $rutaPdf,
                'archivo_cdr' => $rutaCdr
            ]);

        } catch (\Throwable $e) {

            /*
            |-----------------------------------------
            | ERROR
            |-----------------------------------------
            */
            $venta->update([
                'estado_envio' => 'error',
                'mensaje_error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /*
    |-----------------------------------------
    | MAPEAR VENTA → SUNAT
    |-----------------------------------------
    */
    private function mapearVenta($venta)
    {
        return [
            'tipo_documento' => $venta->tipo_documento,
            'serie' => $venta->serie,
            'correlativo' => $venta->correlativo,
            'fecha_emision' => $venta->fecha_emision,
            'moneda' => $venta->moneda,

            'cliente' => [
                'tipo_doc' => $venta->tipo_documento_cliente,
                'num_doc' => $venta->numero_documento_cliente,
                'razon_social' => $venta->nombre_cliente
            ],

            'items' => $venta->detalles->map(function ($d) {
                return [
                    'codigo' => $d->codigo_producto,
                    'descripcion' => $d->descripcion,
                    'unidad' => $d->unidad,
                    'cantidad' => $d->cantidad,
                    'valor_unitario' => $d->valor_unitario
                ];
            })->toArray()
        ];
    }
}