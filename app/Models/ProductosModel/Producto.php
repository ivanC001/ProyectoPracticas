<?php

namespace App\Models\ProductosModel;

use App\Models\VentasModel\DetalleVenta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{

    use SoftDeletes;

    protected $table = 'productos';


    protected $fillable = [

        'codigo',
        'descripcion',
        'categoria',
        'unidad',
        'precio',
        'moneda_precio',
        'stock',
        'activo'

    ];


    /*
    |--------------------------------------------------------------------------
    | Relación con venta_detalles
    |--------------------------------------------------------------------------
    */

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }

}
