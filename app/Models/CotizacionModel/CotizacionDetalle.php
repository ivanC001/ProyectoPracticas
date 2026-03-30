<?php

namespace App\Models\CotizacionModel;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductosModel\Producto;
use App\Models\ProductosModel\Servicio;

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
        'cantidad',
        'precio',
        'subtotal'
    ];

    protected $casts = [
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