<?php

namespace App\Domains\Reportes\Services;

use App\Models\Combustible; // Asegúrate de tener el modelo Combustible correctamente configurado
use Illuminate\Support\Facades\DB;

class ReporteCombustibleService
{
    // Consumo por camión por ruta
    public function consumoPorCamionPorRuta($camion_id, $ruta_id)
    {
        return Combustible::where('ruta_id', $ruta_id)
            ->whereHas('ruta', function($query) use ($camion_id) {
                $query->where('camion_id', $camion_id);
            })
            ->sum('galonesCombustible');
    }

    // Media de consumo por distancia (combustible / km recorrido)
    public function mediaConsumoPorDistancia()
    {
        return Combustible::selectRaw('AVG(galonesCombustible / (kilometraje_final - kilometraje_inicial)) as media_consumo')
            ->whereNotNull('kilometraje_inicial')
            ->whereNotNull('kilometraje_final')
            ->first();
    }

    // Consumo total de todos los camiones en un mes específico
    public function consumoTotalPorMes($mes, $año)
    {
        return Combustible::whereYear('fecha_hora', $año)
            ->whereMonth('fecha_hora', $mes)
            ->sum('galonesCombustible');
    }
}
