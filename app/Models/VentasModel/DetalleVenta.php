<?php
namespace App\Models\VentasModel;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = 'detalle_ventas';

    protected $fillable = [

        'venta_id',
        'tipo_item',
        'item_id',
        'codigo_producto',
        'descripcion',
        'unidad',
        'tip_afe_igv',

        'cantidad',
        'valor_unitario',
        'precio_unitario',

        'descuento',
        'subtotal',
        'igv',
        'total'
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
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
