<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Viatico extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ruta_id',
        'nombre_servicio',
        'fecha',
        'numero_factura',
        'importe',
        'descripcion',
    ];

    // Relación con el modelo Ruta
    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }
}
