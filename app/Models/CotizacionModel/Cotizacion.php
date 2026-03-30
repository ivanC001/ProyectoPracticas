<?php

namespace App\Models\CotizacionModel;

use Illuminate\Database\Eloquent\Model;
use App\Models\ClientesModel\Cliente;

class Cotizacion extends Model
{
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
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalles()
    {
        return $this->hasMany(
            CotizacionDetalle::class,
            'cotizacion_id'
        );
    }
}