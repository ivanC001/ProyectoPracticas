<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class Camion extends Model{

    use HasFactory, SoftDeletes;
    protected $table = 'camiones';

    protected $fillable = [
        'fecha_ingreso',
        'placa_tracto',
        'placa_carreto',
        'color',
        'mtc',
        'foto_camino',
    ];

    protected $dates = ['deleted_at'];
}
