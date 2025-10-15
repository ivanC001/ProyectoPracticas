<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ruta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rutas';

    protected $fillable = [
        // datos del viaje
        'fecha_inicio',
        'fecha_fin',
        'origen',
        'destino',
        // responsable del viaje
        'conductor_id',
        'camion_id',
        // gastos del viaje
        'caja_chica',
        'estado',
        'pago_viaje',
        'ganancia_viaje',
        'observaciones',
    ];

    protected $dates = ['deleted_at'];

    public function conductor()
    {
        return $this->belongsTo(Conductor::class);
    }

    public function camion()
    {
        return $this->belongsTo(Camion::class);
    }

    // relación con viáticos
    public function viaticos()
    {
        return $this->hasMany(Viatico::class, 'ruta_id');
    }

    // relación con combustibles
    public function combustibles()
    {
        return $this->hasMany(Combustible::class, 'ruta_id');
    }

    // relación con peajes
    public function peajes()
    {
        return $this->hasMany(Peaje::class, 'ruta_id');
    }
}
