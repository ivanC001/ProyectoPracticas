<?php

namespace App\Models\ClientesModel;

use Illuminate\Database\Eloquent\Model;
use App\Models\CotizacionModel\Cotizacion;

class Cliente extends Model
{
     protected $table = 'clientes';

    protected $fillable = [
        'tipo_doc',
        'num_doc',
        'razon_social',
        'direccion',
        'email',
        'telefono',
        'estado'
    ];
    protected $casts = [
        'estado' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | SCOPES 
    |--------------------------------------------------------------------------
    */

    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }
     /**
     * Un cliente pertenece a un tipo de documento
     */
       /*
    |----------------------------------------------------------------------
    | RELACIONES
    |----------------------------------------------------------------------
    */
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'cliente_id');
    }
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_doc', 'codigo');
    }

}
