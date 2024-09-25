<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conductor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'conductores';

    protected $fillable = [
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'genero',
        'licencia',
        'tipo_licencia',
        'telefono',
        'email',
        'direccion',
        'ciudad',
    ];

    // Habilitar las columnas de timestamps y soft deletes
    protected $dates = ['deleted_at'];
}


