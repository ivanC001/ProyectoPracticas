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
        'fecha_inicio',
        'fecha_fin',
        'origen',
        'destino',
        'conductor_id',
        'camion_id',
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

    // relacion con viaticos
    public function viaticos()
    {
        return $this->hasMany(Viatico::class, 'ruta_id');
    }

    // relacion con combustible
    public function combustibles()
    {
        return $this->hasMany(Combustible::class, 'ruta_id');
    }
}
