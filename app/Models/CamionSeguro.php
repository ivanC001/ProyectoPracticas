<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CamionSeguro extends Model
{
    protected $table = 'camion_seguros';

    protected $fillable = [
        'camion_id',
        'tipo_seguro',
        'aseguradora',
        'numero_poliza',
        'fecha_inicio',
        'fecha_vencimiento',
        'monto',
        'alertar_dias_antes',
        'activo',
        'ultimo_aviso_enviado_at',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
        'monto' => 'float',
        'activo' => 'boolean',
        'ultimo_aviso_enviado_at' => 'datetime',
    ];

    public function camion()
    {
        return $this->belongsTo(Camion::class, 'camion_id');
    }
}
