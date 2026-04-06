<?php

namespace App\Models\CotizacionModel;

use Illuminate\Database\Eloquent\Model;
use App\Models\ClientesModel\Cliente;
use App\Models\CotizacionModel\CotizacionDetalle;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'cliente_id',
        'asunto',
        'fecha',
        'descripcion_general',
        'notas',
        'medios_pago',
        'subtotal',
        'igv',
        'total',
        'estado'
    ];

    protected $casts = [
        'fecha' => 'date',
        'medios_pago' => 'array',
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
        return $this->hasMany(CotizacionDetalle::class, 'cotizacion_id');
    }
}
