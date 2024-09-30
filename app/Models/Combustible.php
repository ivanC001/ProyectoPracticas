<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Combustible extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ruta_id',
        'num_factura',
        'grifo',
        'fecha_hora',
        'galonesCombustible',
        'importe',
        'kilometraje_inicial',
        'kilometraje_final',
        'tipo_combustible',
    ];

    // Relación con el modelo Ruta
    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }
}
