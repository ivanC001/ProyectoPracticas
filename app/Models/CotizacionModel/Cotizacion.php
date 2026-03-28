<?php

namespace App\Models\CotizacionModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CotizacionModel\CotizacionDetalle;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'cliente_id',
        'fecha',
        'fecha_vencimiento',
        'subtotal',
        'igv',
        'total',
        'estado',
        'observacion'
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_vencimiento' => 'date',
        'subtotal' => 'float',
        'igv' => 'float',
        'total' => 'float'
    ];

    /* 🔥 RELACIONES */

    public function cliente()
    {
        return $this->belongsTo(\App\Models\ClientesModel\Cliente::class, 'cliente_id');
    }

    public function detalles()
    {
        return $this->hasMany(CotizacionDetalle::class, 'cotizacion_id');
    }
}