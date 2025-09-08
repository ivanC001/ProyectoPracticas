<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Peaje extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ruta_id',
        'nombre',
        'importe',
        'fecha_hora',
        'comprobante',
    ];

    // Relación: un peaje pertenece a una ruta
    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }
}
