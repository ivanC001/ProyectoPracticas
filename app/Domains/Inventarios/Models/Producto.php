<?php

namespace App\Domains\Inventarios\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nombre', 'descripcion', 'precio', 'cantidad_stock'];

    // Relación con el modelo ControlProducto
    public function controlProductos()
    {
        return $this->hasMany(ControlProducto::class);
    }

    // Método para obtener el stock actual
    public function stockActual()
    {
        $entradas = $this->controlProductos()->where('tipo_accion', 'entrada')->sum('cantidad');
        $salidas = $this->controlProductos()->where('tipo_accion', 'salida')->sum('cantidad');

        return $this->cantidad_stock + $entradas - $salidas;
    }
}
