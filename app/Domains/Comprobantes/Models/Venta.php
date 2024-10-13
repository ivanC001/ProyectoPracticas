<?php

namespace App\Domains\Comrobantes\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    // Definir la tabla asociada al modelo
    protected $table = 'ventas';

    // Definir los campos que son asignables en masa
    protected $fillable = [
        'tipo_documento',
        'serie',
        'correlativo',
        'fecha_emision',
        'moneda',
        'tipo_documento_cliente',
        'numero_documento_cliente',
        'nombre_cliente',
        'total_venta',
        'total_impuestos',
        'hash_cpe',
        'archivo_xml',
        'archivo_pdf',
        'estado_envio'
    ];

    // Relación con los detalles de la venta (1 a muchos)
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    // Relación con la respuesta de SUNAT (1 a 1)
    public function respuestaSunat()
    {
        return $this->hasOne(RespuestaSunat::class, 'venta_id');
    }
}
