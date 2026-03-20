<?php

namespace App\Models\VentasModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SerieCorrelativo extends Model
{

    protected $table = 'series_correlativo';


    protected $fillable = [

        'tipo_documento',
        'serie',
        'correlativo_actual',
        'activo'

    ];



    /*
    |--------------------------------------------------------------------------
    | Obtener siguiente correlativo
    |--------------------------------------------------------------------------
    */



    public static function obtenerSiguienteCorrelativo($tipoDocumento)
    {
        return DB::transaction(function () use ($tipoDocumento) {

            $serie = self::where('tipo_documento', $tipoDocumento)
                ->where('activo', 1)
                ->lockForUpdate()
                ->first();

            // Si no existe la serie la creamos
            if (!$serie) {

                switch ($tipoDocumento) {
                    case '01': $serieNombre = 'F001'; break; // factura
                    case '03': $serieNombre = 'B001'; break; // boleta
                    case '07': $serieNombre = 'FC01'; break; // nota crédito
                    case '08': $serieNombre = 'FD01'; break; // nota débito
                    case '09': $serieNombre = 'T001'; break; // guía remisión
                    default:
                        throw new \Exception("Tipo de documento no soportado");
                }

                $serie = self::create([
                    'tipo_documento' => $tipoDocumento,
                    'serie' => $serieNombre,
                    'correlativo_actual' => 0,
                    'activo' => 1
                ]);
            }

            $correlativo = $serie->correlativo_actual + 1;

            $serie->correlativo_actual = $correlativo;
            $serie->save();

            return [
                'serie' => $serie->serie,
                'correlativo' => $correlativo
            ];
        });
    }
}