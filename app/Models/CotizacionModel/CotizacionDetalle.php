<?php

namespace App\Models\CotizacionModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductosModel\Producto;
use App\Models\ProductoModel\Servicio;


class CotizacionDetalle extends Model
{
    use HasFactory;

    protected $table = 'cotizacion_detalle';

    protected $fillable = [
        'cotizacion_id',
        'tipo',
        'producto_id',
        'servicio_id',
        'descripcion',
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
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}