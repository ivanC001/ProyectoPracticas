<?php

namespace App\Domains\Reportes\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Reportes\Services\ReporteCombustibleService;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    protected $reporteCombustibleService;

    public function __construct(ReporteCombustibleService $reporteCombustibleService)
    {
        $this->reporteCombustibleService = $reporteCombustibleService;
    }

    // Función para obtener el consumo por camión por ruta
    public function consumoPorCamionPorRuta(Request $request)
    {
        $camion_id = $request->input('camion_id');
        $ruta_id = $request->input('ruta_id');

        $consumo = $this->reporteCombustibleService->consumoPorCamionPorRuta($camion_id, $ruta_id);

        return response()->json(['consumo' => $consumo]);
    }

    // Función para obtener la media de consumo por distancia
    public function mediaConsumoPorDistancia()
    {
        $mediaConsumo = $this->reporteCombustibleService->mediaConsumoPorDistancia();

        return response()->json(['media_consumo' => $mediaConsumo]);
    }

    // Función para obtener el consumo total en un mes específico
    public function consumoTotalPorMes(Request $request)
    {
        $mes = $request->input('mes');
        $año = $request->input('año');

        $consumoTotal = $this->reporteCombustibleService->consumoTotalPorMes($mes, $año);

        return response()->json(['consumo_total' => $consumoTotal]);
    }
}
