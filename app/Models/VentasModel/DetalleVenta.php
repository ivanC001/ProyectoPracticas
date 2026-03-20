<?php

namespace App\Models\VentasModel;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{

    protected $table = 'venta_detalles';


    protected $fillable = [

        'venta_id',
        'codigo_producto',
        'descripcion',
        'unidad',
        'cantidad',
        'valor_unitario',
        'igv',
        'total'

    ];


    /*
    |--------------------------------------------------------------------------
    | Relación con Venta
    |--------------------------------------------------------------------------
    */

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

}