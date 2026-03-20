<?php

namespace App\Http\Controllers\Factura\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Greenter\Model\Response\BillResult;
use App\Services\SunatService;
use App\Services\GuardarComprobantes;
use App\Services\VentaService;
use App\Http\Requests\StoreVentaRequest;
use App\Models\ProductosModel\Producto;
use Illuminate\Validation\ValidationException;
use Greenter\Report\XmlUtils;

class FacturaController extends Controller
{
    public function newventa(StoreVentaRequest $request)
{
    $data = $request->validated();

    return DB::transaction(function () use ($data) {

        /*
        |------------------------------------------------------------------
        | CLIENTE BOLETA
        |------------------------------------------------------------------
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
        |------------------------------------------------------------------
        | OBTENER PRODUCTOS
        |------------------------------------------------------------------
        */
        $codigos = collect($data['items'])->pluck('codigo');

        $productos = Producto::whereIn('codigo', $codigos)
            ->get()
            ->keyBy('codigo');

        /*
        |------------------------------------------------------------------
        | VALIDAR + COMPLETAR
        |------------------------------------------------------------------
        */
        foreach ($data['items'] as $index => &$item) {

            unset($item['descripcion'], $item['unidad']);

            $producto = $productos[$item['codigo']] ?? null;

            if (!$producto) {
                throw ValidationException::withMessages([
                    "items.$index.codigo" => [
                        "El producto {$item['codigo']} no existe"
                    ]
                ]);
            }

            if ($producto->stock < $item['cantidad']) {
                throw ValidationException::withMessages([
                    "items.$index.cantidad" => [
                        "Stock insuficiente para {$producto->descripcion}"
                    ]
                ]);
            }

            // 🔥 DATOS DESDE BD
            $item['descripcion'] = $producto->descripcion;
            $item['unidad'] = $producto->unidad;

            // ⚠️ PRECIO BASE (SIN IGV)
            $item['valor_unitario'] = $producto->precio;

            // 🔥 PRECIO CON IGV
            $item['precio_unitario'] = round($producto->precio * 1.18, 2);

            // 🔥 DESCUENTO
            $item['descuento'] = $item['descuento'] ?? 0;

            // VALIDACIONES
            if ($item['descuento'] < 0) {
                throw ValidationException::withMessages([
                    "items.$index.descuento" => ["Descuento inválido"]
                ]);
            }

            $maxDescuento = $item['cantidad'] * $producto->precio;

            if ($item['descuento'] > $maxDescuento) {
                throw ValidationException::withMessages([
                    "items.$index.descuento" => ["Descuento mayor al valor del producto"]
                ]);
            }
        }

        /*
        |------------------------------------------------------------------
        | SUNAT
        |------------------------------------------------------------------
        */
        $envioSunat = new SunatService();

        $see = $envioSunat->getSee();
        $invoice = $envioSunat->getInvoice($data);

        $result = $see->send($invoice);
        /** @var BillResult $result */

        $xml = $see->getFactory()->getLastXml();
        $cdrZip = $result->isSuccess() ? $result->getCdrZip() : null;

        /*
        |------------------------------------------------------------------
        | ARCHIVOS
        |------------------------------------------------------------------
        */
        $archivosGuardar = new GuardarComprobantes();

        $rutaXml = $archivosGuardar->guardarXml($invoice, $xml);

        $rutaCdr = $cdrZip
            ? $archivosGuardar->guardarCdr($invoice, $cdrZip)
            : null;

        /*
        |------------------------------------------------------------------
        | HASH
        |------------------------------------------------------------------
        */
        $hash = (new XmlUtils())->getHashSign($xml);

        /*
        |------------------------------------------------------------------
        | RESPUESTA SUNAT
        |------------------------------------------------------------------
        */
        $sunatResponse = $envioSunat->sunatResponse($result);

        /*
        |------------------------------------------------------------------
        | DESCONTAR STOCK
        |------------------------------------------------------------------
        */
        if ($sunatResponse['success']) {
            foreach ($data['items'] as $item) {
                $producto = $productos[$item['codigo']];
                $producto->decrement('stock', $item['cantidad']);
            }
        }

        /*
        |------------------------------------------------------------------
        | PDF
        |------------------------------------------------------------------
        */
        $rutaPdf = null;

        if ($sunatResponse['success']) {
            $rutaPdf = $archivosGuardar->generarPdf($invoice);
        }

        /*
        |------------------------------------------------------------------
        | COMPROBANTE
        |------------------------------------------------------------------
        */
        $numeroComprobante =
            $invoice->getSerie() . '-' . $invoice->getCorrelativo();

        $totales = $envioSunat->calcularTotales($data['items']);

        /*
        |------------------------------------------------------------------
        | GUARDAR VENTA
        |------------------------------------------------------------------
        */
        $ventaService = new VentaService();

        $ventaService->guardarVenta(
            $data,
            $invoice,
            $totales,
            $hash,
            $sunatResponse,
            $rutaXml,
            $rutaPdf,
            $rutaCdr
        );

        return response()->json([
            'success' => $sunatResponse['success'] ?? false,
            'comprobante' => [
                'numero' => $numeroComprobante,
            ],
            'cliente' => [
                'doc' => $data['cliente']['num_doc'] ?? null,
                'razon_social' => $data['cliente']['razon_social'] ?? null
            ],
            'sunat' => $sunatResponse['cdrRespuesta']
                ?? $sunatResponse['error']
                ?? null
        ], 200);
    });
}
}