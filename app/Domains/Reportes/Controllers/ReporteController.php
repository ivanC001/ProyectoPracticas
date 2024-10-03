<?php

namespace App\Domains\Reportes\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Reportes\Services\ReporteViaticosService;
use App\Domains\Reportes\Services\ReporteCombustibleService;
use App\Domains\Reportes\Services\ReporteRutasService;
use App\Domains\Reportes\Services\ReporteExportService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    protected $reporteViaticosService;
    protected $reporteCombustibleService;
    protected $reporteRutasService;
    protected $reporteExportService;

    public function __construct(ReporteViaticosService $reporteViaticosService, ReporteCombustibleService $reporteCombustibleService, ReporteRutasService $reporteRutasService, ReporteExportService $reporteExportService)
    {
        $this->reporteViaticosService = $reporteViaticosService;
        $this->reporteCombustibleService = $reporteCombustibleService;
        $this->reporteRutasService = $reporteRutasService;
        $this->reporteExportService = $reporteExportService;
    }


    ///  viaticos

    public function viaticosPorRuta($id): JsonResponse
    {
        $data = $this->reporteViaticosService->obtenerViaticosPorRuta($id);
        if (!$data) {
            return response()->json(['error' => 'Ruta no encontrada'], 404);
        }

        return response()->json($data);
    }
    // conbustible
    public function combustiblePorRuta($id): JsonResponse
    {
        $data = $this->reporteCombustibleService->obtenerCombustiblePorRuta($id);
        if (!$data) {
            return response()->json(['error' => 'Ruta no encontrada'], 404);
        }

        return response()->json($data);
    }

   // rutas

    public function reporteCompletoPorRuta($id): JsonResponse
    {
        $viaticos = $this->reporteViaticosService->obtenerViaticosPorRuta($id);
        $combustible = $this->reporteCombustibleService->obtenerCombustiblePorRuta($id);

        if (!$viaticos && !$combustible) {
            return response()->json(['error' => 'Ruta no encontrada'], 404);
        }
        $data = [
            'viaticos' => $viaticos,
            'combustibles' => $combustible,
        ];
        return response()->json($data);
    }


    /// por rango y paginacion

    public function rutasConsumos(Request $request): JsonResponse
    {
        // Validar los parámetros de entrada
        $request->validate([
            'id' => 'nullable|integer',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'exportar' => 'nullable|integer',
        ]);

        $id = $request->input('id');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $exportar = $request->input('exportar');
        // Caso 1: Si se envía el ID, obtenemos los datos por ID
        if ($id) {
            $ruta = $this->reporteRutasService->obtenerRutaPorId($id);
            if (!$ruta) {
                return response()->json(['error' => 'Ruta no encontrada'], 404);
            }
            if ($exportar) {
                return $this->reporteExportService->exportarRutas([$ruta]);
            }

            $viaticos = $this->reporteViaticosService->obtenerTotalViaticosPorRuta($id);
            $combustible = $this->reporteCombustibleService->obtenerTotalCombustiblePorRuta($id);

            return response()->json([
                'ruta' => $ruta,
                'total_viaticos' => $viaticos,
                'total_combustible' => $combustible,
            ]);
        }

        // Caso 2: Si se envían fechas, obtenemos los datos filtrados por rango de fechas
        if ($fechaInicio && $fechaFin) {
            $rutas = $this->reporteRutasService->obtenerRutasPorRangoDeFechas($fechaInicio, $fechaFin);
        } elseif ($fechaInicio) {
            // Si solo se envía una fecha
            $rutas = $this->reporteRutasService->obtenerRutasPorFecha($fechaInicio);
        } else {
            // Caso 3: Si no se envía nada, devolvemos todas las rutas con totales paginadas
            $rutas = $this->reporteRutasService->obtenerTodasLasRutasPaginadas();
        }

        // Transformamos las rutas para agregar manualmente los totales de viáticos y combustible
        $rutas->getCollection()->transform(function ($ruta) {
            $ruta->total_viaticos = $this->reporteViaticosService->obtenerTotalViaticosPorRuta($ruta->id);
            $ruta->total_combustible = $this->reporteCombustibleService->obtenerTotalCombustiblePorRuta($ruta->id);
            return $ruta;
        });

        return response()->json($rutas);
    }
}
