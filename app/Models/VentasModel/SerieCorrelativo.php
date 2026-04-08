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
    |-----------------------------------------
    | OBTENER CORRELATIVO SEGURO
    |-----------------------------------------
    */

    public static function obtenerSiguienteCorrelativo($tipoDocumento)
    {
        return DB::transaction(function () use ($tipoDocumento) {

            $serie = self::where('tipo_documento', $tipoDocumento)
                ->where('activo', 1)
                ->lockForUpdate()
                ->first();

            if (!$serie) {

                switch ($tipoDocumento) {
                    case '01': $serieNombre = 'F001'; break;
                    case '03': $serieNombre = 'B001'; break;
                    case '07': $serieNombre = 'FC01'; break;
                    case '08': $serieNombre = 'FD01'; break;
                    case '09': $serieNombre = 'T001'; break;
                    default:
                        throw new \Exception("Tipo de documento no soportado");
                }

                try {
                    $serie = self::create([
                        'tipo_documento' => $tipoDocumento,
                        'serie' => $serieNombre,
                        'correlativo_actual' => 0,
                        'activo' => 1
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {

                    // 🔥 ya fue creado por otro proceso
                    $serie = self::where('tipo_documento', $tipoDocumento)
                        ->where('serie', $serieNombre)
                        ->lockForUpdate()
                        ->first();
                }
            }

            $correlativo = $serie->correlativo_actual + 1;

            $serie->correlativo_actual = $correlativo;
            $serie->save();

            return [
                'serie' => $serie->serie,
                'correlativo' => $correlativo,
                'numero_comprobante' =>
                    $serie->serie . '-' . str_pad($correlativo, 8, '0', STR_PAD_LEFT)
            ];
        });
    }
}