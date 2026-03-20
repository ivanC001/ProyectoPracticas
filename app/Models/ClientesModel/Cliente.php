<?php

namespace App\Models\ClientesModel;

use Illuminate\Database\Eloquent\Model;

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
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_doc', 'codigo');
    }

}
