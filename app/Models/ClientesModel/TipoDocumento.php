<?php

namespace App\Models\ClientesModel;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
     protected $table = 'tipo_documentos';

    protected $fillable = [
        'codigo',
        'descripcion',
        'longitud',
        'estado'
    ];

    /**
     * Un tipo de documento tiene muchos clientes
     */
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'tipo_doc', 'codigo');
    }
}
