<?php

namespace App\Models\ProductosModel;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductosModel\ServicioDetalle;

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'precio',
        'costo',
        'duracion_estimada',
        'activo'
    ];

    protected $casts = [
        'precio' => 'float',
        'costo' => 'float',
        'duracion_estimada' => 'integer',
        'activo' => 'boolean'
    ];

    /* 🔥 RELACIONES */

    // 👉 pasos del servicio
    public function pasos()
    {
        return $this->hasMany(ServicioDetalle::class, 'servicio_id')
                    ->orderBy('orden');
    }

    // 👉 uso en cotización
    public function cotizaciones()
    {
        return $this->hasMany(
            \App\Models\CotizacionModel\CotizacionDetalle::class,
            'servicio_id'
        );
    }
}