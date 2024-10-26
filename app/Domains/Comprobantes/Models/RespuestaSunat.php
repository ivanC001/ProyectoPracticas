<?php

namespace App\Domains\Comprobantes\Models;

use Illuminate\Database\Eloquent\Model;

class RespuestaSunat extends Model
{
    // Definir la tabla asociada al modelo
    protected $table = 'respuestas_sunat';

    // Definir los campos que son asignables en masa
    protected $fillable = [
        'venta_id',
        'codigo_respuesta',
        'mensaje_respuesta',
        'ticket_consulta',
        'respuesta_completa'
    ];

    // Relación inversa con la tabla ventas (muchos a uno)
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}

