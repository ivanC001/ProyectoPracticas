<?php
namespace App\Services;

use App\Models\VentasModel\Venta;
use App\Models\VentasModel\SerieCorrelativo;
use App\Models\VentasModel\DetalleVenta;
use Illuminate\Support\Facades\DB;

class VentaService
{

    /*
    |--------------------------------------------------------------------------
    | Guardar venta en base de datos
    |--------------------------------------------------------------------------
    */


    public function guardarVenta($data,$invoice,$totales,$hash,$sunatResponse,$rutaXml,$rutaPdf,$rutaCdr) {

        $numeroComprobante =
            $invoice->getSerie() . '-' . $invoice->getCorrelativo();

        return Venta::create([

            'tipo_documento' => $data['tipo_documento'],
            'tipo_operacion' => '0101',

            'serie' => $invoice->getSerie(),
            'correlativo' => $invoice->getCorrelativo(),
            'numero_comprobante' => $numeroComprobante,

            'fecha_emision' => $data['fecha_emision'],
            'moneda' => $data['moneda'],

            'tipo_documento_cliente' => $data['cliente']['tipo_doc'],
            'numero_documento_cliente' => $data['cliente']['num_doc'],
            'nombre_cliente' => $data['cliente']['razon_social'],

            'total_venta' => $totales['total'],
            'total_impuestos' => $totales['igv'],

            'codigo_respuesta_sunat'
                => $sunatResponse['cdrRespuesta']['code'] ?? null,

            'descripcion_respuesta_sunat'
                => $sunatResponse['cdrRespuesta']['description'] ?? null,

            'hash_cpe' => $hash,

            'archivo_xml' => $rutaXml,
            'archivo_pdf' => $rutaPdf,
            'cdr_zip' => $rutaCdr,

            'estado_envio'
                => $sunatResponse['success']
                ? 'aceptado'
                : 'rechazado'

        ]);
    }
    public function guardarVentaPendiente($data)
{
    return DB::transaction(function () use ($data) {

        // 🔥 correlativo seguro
        $correlativo = SerieCorrelativo::obtenerSiguienteCorrelativo(
            $data['tipo_documento']
        );

        $venta = Venta::create([

            'tipo_documento' => $data['tipo_documento'],
            'tipo_operacion' => '0101',

            'serie' => $correlativo['serie'],
            'correlativo' => $correlativo['correlativo'],
            'numero_comprobante' => $correlativo['numero_comprobante'],

            'fecha_emision' => $data['fecha_emision'],
            'moneda' => $data['moneda'],

            'tipo_documento_cliente' => $data['cliente']['tipo_doc'] ?? null,
            'numero_documento_cliente' => $data['cliente']['num_doc'] ?? null,
            'nombre_cliente' => $data['cliente']['razon_social'] ?? 'CLIENTES VARIOS',

            'estado_envio' => 'pendiente'
        ]);

        foreach ($data['items'] as $item) {

            $subtotal = $item['cantidad'] * $item['valor_unitario'];
            $igv = round($subtotal * 0.18, 2);

            DetalleVenta::create([
                'venta_id' => $venta->id,
                'codigo_producto' => $item['codigo'],
                'descripcion' => $item['descripcion'],
                'unidad' => $item['unidad'] ?? 'NIU',

                'cantidad' => $item['cantidad'],
                'valor_unitario' => $item['valor_unitario'],
                'precio_unitario' => round($item['valor_unitario'] * 1.18, 2),

                'descuento' => $item['descuento'] ?? 0,
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $subtotal + $igv,
            ]);
        }

        return $venta;
    });
}
}