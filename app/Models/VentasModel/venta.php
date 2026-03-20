<?php

namespace App\Models\VentasModel;

use App\Models\VentasModel\DetalleVenta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    protected $table = 'ventas';

    protected $fillable = [

        // Documento
        'tipo_documento',
        'tipo_operacion',
        'serie',
        'correlativo',
        'numero_comprobante',
        'fecha_emision',
        'moneda',

        // Cliente
        'tipo_documento_cliente',
        'numero_documento_cliente',
        'nombre_cliente',

        // Totales
        'total_venta',
        'total_impuestos',
        'descuentos',

        // Respuesta SUNAT
        'codigo_respuesta_sunat',
        'descripcion_respuesta_sunat',

        // Archivos
        'hash_cpe',
        'archivo_xml',
        'archivo_pdf',
        'cdr_zip',

        // Estado
        'estado_envio'
    ];



    protected $casts = [

        'fecha_emision' => 'datetime',
        'total_venta' => 'decimal:2',
        'total_impuestos' => 'decimal:2'

    ];



    /*
    |--------------------------------------------------------------------------
    | Generar Serie según tipo de comprobante
    |--------------------------------------------------------------------------
    */

    public static function obtenerSerie($tipoDocumento)
    {
        return $tipoDocumento == '01' ? 'F001' : 'B001';
    }



    /*
    |--------------------------------------------------------------------------
    | Obtener correlativo automático
    |--------------------------------------------------------------------------
    */

    public static function obtenerCorrelativo($serie)
    {
        $ultimo = self::where('serie', $serie)
            ->max('correlativo');

        return $ultimo ? $ultimo + 1 : 1;
    }



    /*
    |--------------------------------------------------------------------------
    | Accessor Número completo del comprobante
    |--------------------------------------------------------------------------
    */

    public function getNumeroCompletoAttribute()
    {
        return $this->serie . '-' . str_pad($this->correlativo, 6, '0', STR_PAD_LEFT);
    }



    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }

}