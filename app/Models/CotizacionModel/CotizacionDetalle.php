<?php

namespace App\Models\CotizacionModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductosModel\Producto;

class CotizacionDetalle extends Model
{
    use HasFactory;

    protected $table = 'cotizacion_detalle';

    protected $fillable = [
        'cotizacion_id',
        'item_id',
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
        return $this->belongsTo(Cotizacion::class);
    }

    public function item()
    {
        return $this->belongsTo(Producto::class);
    }
}