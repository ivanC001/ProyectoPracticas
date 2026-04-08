<?php
namespace App\Models\VentasModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    protected $table = 'ventas';

    protected $fillable = [

        // 📄 Documento
        'tipo_documento',
        'tipo_operacion',
        'serie',
        'correlativo',
        'numero_comprobante',
        'fecha_emision',
        'moneda',

        // 👤 Cliente
        'tipo_documento_cliente',
        'numero_documento_cliente',
        'nombre_cliente',

        // 💰 Totales
        'total_venta',
        'total_impuestos',

        // 🚀 SUNAT
        'sunat_enviado',
        'fecha_envio_sunat',
        'estado_envio',
        'codigo_respuesta_sunat',
        'descripcion_respuesta_sunat',
        'mensaje_error',

        // 📂 Archivos
        'hash_cpe',
        'archivo_xml',
        'archivo_pdf',
        'archivo_cdr',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'sunat_enviado' => 'boolean',
        'total_venta' => 'decimal:2',
        'total_impuestos' => 'decimal:2',
    ];

    /*
    |-----------------------------------------
    | RELACIONES
    |-----------------------------------------
    */

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    /*
    |-----------------------------------------
    | ACCESSOR
    |-----------------------------------------
    */

    public function getNumeroCompletoAttribute()
    {
        return $this->serie . '-' . str_pad($this->correlativo, 8, '0', STR_PAD_LEFT);
    }
}