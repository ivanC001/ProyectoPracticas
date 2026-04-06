<?php

namespace App\Models\ProductosModel;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductosModel\Servicio;

class ServicioDetalle extends Model
{
    protected $table = 'servicio_detalles';

    protected $fillable = [
        'servicio_id',
        'descripcion',
        'orden'
    ];

    /* 🔥 RELACIONES */

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}