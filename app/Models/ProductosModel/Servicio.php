<?php

namespace App\Models\ProductosModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [

        // 🔹 Básico
        'codigo',
        'nombre',
        'descripcion',
        'precio',
        'costo',
        'duracion_estimada',

        // 🔹 Recursos
        'requiere_personal',
        'cantidad_personal',
        'requiere_equipo',
        'equipos_descripcion',

        // 🔹 Ubicación
        'tipo_servicio',
        'requiere_transporte',

        // 🔹 Comercial
        'condiciones',
        'requisitos_cliente',
        'garantia_dias',

        // 🔹 Clasificación
        'nivel_servicio',
        'prioridad',

        // 🔹 Otros
        'instrucciones',
        'observaciones_internas',
        'frecuencia',
        'recurrente_cada',

        'activo'
    ];

    protected $casts = [
        'precio' => 'float',
        'costo' => 'float',
        'duracion_estimada' => 'integer',

        'requiere_personal' => 'boolean',
        'requiere_equipo' => 'boolean',
        'requiere_transporte' => 'boolean',
        'activo' => 'boolean',

        'garantia_dias' => 'integer',
        'cantidad_personal' => 'integer',
    ];

    /* 🔥 RELACIONES */

    public function detalles()
    {
        return $this->hasMany(
            \App\Models\CotizacionModel\CotizacionDetalle::class,
            'servicio_id'
        );
    }
}