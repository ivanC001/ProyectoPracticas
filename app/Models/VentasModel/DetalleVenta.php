<?php
namespace App\Models\VentasModel;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = 'detalle_ventas';

    protected $fillable = [

        'venta_id',
        'codigo_producto',
        'descripcion',
        'unidad',

        'cantidad',
        'valor_unitario',
        'precio_unitario',

        'descuento',
        'subtotal',
        'igv',
        'total'
    ];

    /*
    |-----------------------------------------
    | RELACIÓN
    |-----------------------------------------
    */

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}