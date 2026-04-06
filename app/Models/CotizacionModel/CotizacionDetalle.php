<?php

namespace App\Models\CotizacionModel;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductosModel\Producto;
use App\Models\ProductosModel\Servicio;
use App\Models\CotizacionModel\Cotizacion;

class CotizacionDetalle extends Model
{
    protected $table = 'cotizacion_detalles';

    protected $fillable = [
        'cotizacion_id',
        'tipo',
        'producto_id',
        'servicio_id',
        'codigo_item',
        'nombre_item',
        'unidad',
        'detalle_servicio', // 🔥 NUEVO (JSON)
        'cantidad',
        'precio',
        'subtotal'
    ];

    protected $casts = [
        'detalle_servicio' => 'array', // 🔥 CLAVE
        'cantidad' => 'float',
        'precio' => 'float',
        'subtotal' => 'float'
    ];

    /* 🔥 RELACIONES */

    public function cotizacion()
    {
        return $this->belongsTo(
            Cotizacion::class,
            'cotizacion_id'
        );
    }

    public function producto()
    {
        return $this->belongsTo(
            Producto::class,
            'producto_id'
        );
    }

    public function servicio()
    {
        return $this->belongsTo(
            Servicio::class,
            'servicio_id'
        );
    }
}