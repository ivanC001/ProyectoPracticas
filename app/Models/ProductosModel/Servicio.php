<?php

namespace App\Models\ProductoModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'activo'
    ];

    protected $casts = [
        'precio' => 'float',
        'activo' => 'boolean'
    ];

    /* 🔥 RELACIONES */

    public function detalles()
    {
        return $this->hasMany(\App\Models\CotizacionModel\CotizacionDetalle::class, 'servicio_id');
    }
}