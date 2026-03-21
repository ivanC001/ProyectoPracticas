<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use App\Services\SunatService;
use App\Services\GuardarComprobantes;
use App\Services\VentaService;
use App\Models\ProductosModel\Producto;
use Greenter\Report\XmlUtils;
use Greenter\Model\Response\BillResult;


class ProcesarFacturaJob implements ShouldQueue
{
    use Queueable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        DB::transaction(function () {

            $data = $this->data;

            /*
            |-----------------------------------------
            | CLIENTE BOLETA
            |-----------------------------------------
            */
            if (
                $data['tipo_documento'] == '03' &&
                (!isset($data['cliente']) || empty($data['cliente']['num_doc']))
            ) {
                $data['cliente'] = [
                    'tipo_doc' => '0',
                    'num_doc' => '0',
                    'razon_social' => 'CLIENTES VARIOS'
                ];
            }

            /*
            |-----------------------------------------
            | PRODUCTOS
            |-----------------------------------------
            */
            $codigos = collect($data['items'])->pluck('codigo');

            $productos = Producto::whereIn('codigo', $codigos)
                ->get()
                ->keyBy('codigo');

            foreach ($data['items'] as &$item) {

                $producto = $productos[$item['codigo']];

                $item['descripcion'] = $producto->descripcion;
                $item['unidad'] = $producto->unidad;
                $item['valor_unitario'] = $producto->precio;
                $item['precio_unitario'] = round($producto->precio * 1.18, 2);
                $item['descuento'] = $item['descuento'] ?? 0;
            }

            /*
            |-----------------------------------------
            | SUNAT
            |-----------------------------------------
            */
            $envioSunat = new SunatService();

            $see = $envioSunat->getSee();
            $invoice = $envioSunat->getInvoice($data);

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
            $sunatResponse = $envioSunat->sunatResponse($result);

            /*
            |-----------------------------------------
            | STOCK
            |-----------------------------------------
            */
            if ($sunatResponse['success']) {
                foreach ($data['items'] as $item) {
                    $producto = $productos[$item['codigo']];
                    $producto->decrement('stock', $item['cantidad']);
                }
            }

            /*
            |-----------------------------------------
            | PDF
            |-----------------------------------------
            */
            $rutaPdf = null;

            if ($sunatResponse['success']) {
                $rutaPdf = $archivos->generarPdf($invoice);
            }

            /*
            |-----------------------------------------
            | GUARDAR
            |-----------------------------------------
            */
            $ventaService = new VentaService();

            $ventaService->guardarVenta(
                $data,
                $invoice,
                $envioSunat->calcularTotales($data['items']),
                $hash,
                $sunatResponse,
                $rutaXml,
                $rutaPdf,
                $rutaCdr
            );

        });
    }
}