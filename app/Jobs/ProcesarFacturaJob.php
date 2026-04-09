<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use App\Services\FacturaPdfRenderService;
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
            'estado_envio' => 'procesando',
            'mensaje_error' => null,
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
                    if (($item->tipo_item ?? 'producto') !== 'producto') {
                        continue;
                    }

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
                $venta->refresh()->load('detalles');
                $pdfBinary = (new FacturaPdfRenderService())->render($venta);
                $rutaPdf = $archivos->guardarPdfPorVenta($venta, $pdfBinary);
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
                    => $sunatResponse['cdrRespuesta']['code']
                    ?? data_get($sunatResponse, 'error.code'),

                'descripcion_respuesta_sunat'
                    => $sunatResponse['cdrRespuesta']['description']
                    ?? data_get($sunatResponse, 'error.message'),

                'mensaje_error' => $sunatResponse['success']
                    ? null
                    : (data_get($sunatResponse, 'error.message') ?: 'SUNAT rechazo el comprobante'),

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
            'tipo_operacion' => $venta->tipo_operacion ?: '0101',
            'serie' => $venta->serie,
            'correlativo' => $venta->correlativo,
            'fecha_emision' => optional($venta->fecha_emision)->format('Y-m-d H:i:s'),
            'moneda' => $venta->moneda,
            'forma_pago' => $venta->forma_pago,
            'observacion' => $venta->observacion,
            'credito' => [
                'cuotas' => $venta->credito_total_cuotas,
                'monto_pendiente' => (float) ($venta->credito_monto_pendiente ?? 0),
                'fecha_vencimiento' => optional($venta->credito_fecha_vencimiento)->format('Y-m-d'),
            ],
            'detraccion' => [
                'aplica' => (bool) ($venta->detraccion_aplica ?? false),
                'codigo' => $venta->detraccion_codigo,
                'porcentaje' => (float) ($venta->detraccion_porcentaje ?? 0),
                'monto' => (float) ($venta->detraccion_monto ?? 0),
                'cuenta' => $venta->detraccion_cuenta,
                'medio_pago' => $venta->detraccion_medio_pago,
            ],

            'cliente' => [
                'tipo_doc' => $venta->tipo_documento_cliente,
                'num_doc' => $venta->numero_documento_cliente,
                'razon_social' => $venta->nombre_cliente
            ],

            'items' => $venta->detalles->map(function ($d) {
                return [
                    'codigo' => $d->codigo_producto,
                    'tipo_item' => $d->tipo_item ?? 'producto',
                    'item_id' => $d->item_id,
                    'descripcion' => $d->descripcion,
                    'unidad' => $d->unidad,
                    'cantidad' => $d->cantidad,
                    'valor_unitario' => $d->valor_unitario,
                    'descuento' => $d->descuento,
                    'tip_afe_igv' => $d->tip_afe_igv,
                ];
            })->toArray()
        ];
    }
}
