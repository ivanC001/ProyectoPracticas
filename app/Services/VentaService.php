<?php
namespace App\Services;

use App\Models\VentasModel\Venta;

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
}