<?php

namespace App\Domains\Inventarios\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ControlProducto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'producto_id',
        'tipo_accion',
        'cantidad',
        'descripcion'
    ];

    // Relación inversa con el modelo Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
