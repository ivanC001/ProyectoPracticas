<?php

namespace App\Domains\Comrobantes\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    // Definir la tabla asociada al modelo
    protected $table = 'detalle_ventas';

    // Definir los campos que son asignables en masa
    protected $fillable = [
        'venta_id',
        'codigo_producto',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'igv',
        'total'
    ];

    // Relación inversa con la tabla ventas (muchos a uno)
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
