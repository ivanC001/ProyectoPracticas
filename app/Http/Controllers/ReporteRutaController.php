<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReporteRutaController extends Controller
{
    public function index(Request $request)
    {
        $query = Ruta::query()
            ->with(['conductor.camion', 'camion'])
            ->withSum('viaticos', 'importe')
            ->withSum('combustibles', 'importe')
            ->withSum('peajes', 'importe')
            ->withCount(['viaticos', 'combustibles', 'peajes']);

        $this->applyFilters($query, $request);
        $kpiQuery = clone $query;

        $rutas = $query->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        $data = collect($rutas->items())
            ->map(fn ($ruta) => $this->transformRutaResumen($ruta))
            ->values();

        $kpisBase = $kpiQuery->get();
        $kpis = $this->buildKpis($kpisBase);
        $analitica = $this->buildAnalitica($data, $kpis, $kpisBase->pluck('id')->all());

        return response()->json([
            'success' => true,
            'message' => 'Reporte de rutas',
            'data' => $data,
            'kpis' => $kpis,
            'analitica' => $analitica,
            'pagination' => [
                'total' => $rutas->total(),
                'per_page' => $rutas->perPage(),
                'current_page' => $rutas->currentPage(),
                'last_page' => $rutas->lastPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $ruta = $this->buildRutaDetailQuery()->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detalle del reporte de ruta',
            'data' => $this->transformRutaDetalle($ruta),
        ]);
    }

    public function pdf($id)
    {
        $ruta = $this->buildRutaDetailQuery()->findOrFail($id);
        $detalle = $this->transformRutaDetalle($ruta);

        $html = view('reporte.pdf', [
            'ruta' => $detalle,
            'empresa' => [
                'razon_social' => config('empresa.razon_social'),
                'ruc' => config('empresa.ruc'),
                'direccion' => config('empresa.direccion'),
                'telefono' => config('empresa.telefono'),
            ],
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="reporte-ruta-' . $ruta->id . '.pdf"',
        ]);
    }

    private function buildRutaDetailQuery()
    {
        return Ruta::with([
            'conductor.camion',
            'camion',
            'viaticos' => fn ($q) => $q->orderBy('fecha'),
            'combustibles' => fn ($q) => $q->orderBy('fecha_hora'),
            'peajes' => fn ($q) => $q->orderBy('fecha_hora'),
        ])
            ->withSum('viaticos', 'importe')
            ->withSum('combustibles', 'importe')
            ->withSum('peajes', 'importe');
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('origen', 'like', "%{$search}%")
                    ->orWhere('destino', 'like', "%{$search}%")
                    ->orWhereHas('conductor', function ($conductorQuery) use ($search) {
                        $conductorQuery->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                    })
                    ->orWhereHas('camion', function ($camionQuery) use ($search) {
                        $camionQuery->where('placa_tracto', 'like', "%{$search}%")
                            ->orWhere('placa_carreto', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_fin', '<=', $request->fecha_fin);
        }
    }

    private function buildKpis(Collection $rutas): array
    {
        $totalViaticos = round((float) $rutas->sum('viaticos_sum_importe'), 2);
        $totalCombustible = round((float) $rutas->sum('combustibles_sum_importe'), 2);
        $totalPeajes = round((float) $rutas->sum('peajes_sum_importe'), 2);
        $totalGastos = round($totalViaticos + $totalCombustible + $totalPeajes, 2);
        $totalIngresos = round((float) $rutas->sum('pago_viaje'), 2);
        $utilidadNeta = round($totalIngresos - $totalGastos, 2);
        $margenNeto = $totalIngresos > 0
            ? round(($utilidadNeta / $totalIngresos) * 100, 2)
            : null;

        $rutasRentables = 0;
        $rutasPerdida = 0;
        $rutasEquilibrio = 0;

        foreach ($rutas as $ruta) {
            $ingresos = (float) ($ruta->pago_viaje ?? 0);
            $gastos = (float) ($ruta->viaticos_sum_importe ?? 0)
                + (float) ($ruta->combustibles_sum_importe ?? 0)
                + (float) ($ruta->peajes_sum_importe ?? 0);
            $utilidad = $ingresos - $gastos;

            if ($utilidad > 0.009) {
                $rutasRentables++;
                continue;
            }

            if ($utilidad < -0.009) {
                $rutasPerdida++;
                continue;
            }

            $rutasEquilibrio++;
        }

        return [
            'total_rutas' => $rutas->count(),
            'total_viaticos' => $totalViaticos,
            'total_combustible' => $totalCombustible,
            'total_peajes' => $totalPeajes,
            'total_gastos' => $totalGastos,
            'total_ingresos' => $totalIngresos,
            'utilidad_neta' => $utilidadNeta,
            'margen_neto_pct' => $margenNeto,
            'rutas_rentables' => $rutasRentables,
            'rutas_perdida' => $rutasPerdida,
            'rutas_equilibrio' => $rutasEquilibrio,
            'ticket_promedio_ruta' => $rutas->count() > 0
                ? round($totalIngresos / $rutas->count(), 2)
                : 0.0,
        ];
    }

    private function buildAnalitica(Collection $rutasResumen, array $kpis, array $rutaIds): array
    {
        $totalGastos = (float) ($kpis['total_gastos'] ?? 0);

        $mapRanking = function (Collection $collection, string $campo) {
            return $collection->take(5)->map(fn (array $ruta) => [
                'id' => $ruta['id'],
                'ruta' => trim(($ruta['origen'] ?? '-') . ' -> ' . ($ruta['destino'] ?? '-')),
                'conductor' => $ruta['conductor'] ?: '-',
                'monto' => (float) ($ruta['totales'][$campo] ?? 0),
                'ingresos' => (float) ($ruta['totales']['ingresos'] ?? 0),
                'gastos' => (float) ($ruta['totales']['gastos'] ?? 0),
            ])->values();
        };

        $topRentables = $mapRanking(
            $rutasResumen->sortByDesc(fn (array $ruta) => (float) ($ruta['totales']['utilidad'] ?? 0))->values(),
            'utilidad'
        );

        $topCostosas = $mapRanking(
            $rutasResumen->sortByDesc(fn (array $ruta) => (float) ($ruta['totales']['gastos'] ?? 0))->values(),
            'gastos'
        );

        $serviceExpression = "COALESCE(NULLIF(TRIM(nombre_servicio), ''), 'Sin categoria')";

        $viaticosPorServicio = empty($rutaIds)
            ? collect()
            : DB::query()
                ->fromSub(function ($query) use ($rutaIds, $serviceExpression) {
                    $query->from('viaticos')
                        ->selectRaw("{$serviceExpression} as servicio, importe")
                        ->whereIn('ruta_id', $rutaIds)
                        ->whereNull('deleted_at');
                }, 'v')
                ->selectRaw('servicio, COUNT(*) as cantidad, SUM(importe) as total, AVG(importe) as promedio')
                ->groupBy('servicio')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
                ->map(fn ($item) => [
                    'servicio' => $item->servicio ?: 'Sin categoria',
                    'cantidad' => (int) $item->cantidad,
                    'total' => round((float) $item->total, 2),
                    'promedio' => round((float) $item->promedio, 2),
                ])->values();

        return [
            'gastos_por_tipo' => [
                $this->buildGastoItem('Viaticos', (float) ($kpis['total_viaticos'] ?? 0), $totalGastos),
                $this->buildGastoItem('Combustible', (float) ($kpis['total_combustible'] ?? 0), $totalGastos),
                $this->buildGastoItem('Peajes', (float) ($kpis['total_peajes'] ?? 0), $totalGastos),
            ],
            'viaticos_por_servicio' => $viaticosPorServicio,
            'top_rutas_utilidad' => $topRentables,
            'top_rutas_gasto' => $topCostosas,
        ];
    }

    private function buildGastoItem(string $nombre, float $monto, float $totalGastos): array
    {
        return [
            'nombre' => $nombre,
            'monto' => round($monto, 2),
            'porcentaje' => $totalGastos > 0 ? round(($monto / $totalGastos) * 100, 2) : 0.0,
        ];
    }

    private function transformRutaResumen(Ruta $ruta): array
    {
        $viaticos = (float) ($ruta->viaticos_sum_importe ?? 0);
        $combustible = (float) ($ruta->combustibles_sum_importe ?? 0);
        $peajes = (float) ($ruta->peajes_sum_importe ?? 0);
        $gastos = $viaticos + $combustible + $peajes;
        $ingresos = (float) ($ruta->pago_viaje ?? 0);
        $utilidad = $ingresos - $gastos;
        $margen = $ingresos > 0 ? round(($utilidad / $ingresos) * 100, 2) : null;
        $gastoVsIngreso = $ingresos > 0 ? round(($gastos / $ingresos) * 100, 2) : null;

        return [
            'id' => $ruta->id,
            'origen' => $ruta->origen,
            'destino' => $ruta->destino,
            'fecha_inicio' => optional($ruta->fecha_inicio)->format('Y-m-d') ?: $ruta->fecha_inicio,
            'fecha_fin' => optional($ruta->fecha_fin)->format('Y-m-d') ?: $ruta->fecha_fin,
            'estado' => $ruta->estado,
            'conductor' => trim(($ruta->conductor->nombre ?? '') . ' ' . ($ruta->conductor->apellido ?? '')),
            'unidad' => [
                'tracto' => $ruta->camion->placa_tracto ?? $ruta->conductor?->camion?->placa_tracto,
                'trailer' => $ruta->camion->placa_carreto ?? $ruta->conductor?->camion?->placa_carreto,
            ],
            'totales' => [
                'viaticos' => round($viaticos, 2),
                'combustible' => round($combustible, 2),
                'peajes' => round($peajes, 2),
                'gastos' => round($gastos, 2),
                'ingresos' => round($ingresos, 2),
                'utilidad' => round($utilidad, 2),
                'margen_pct' => $margen,
                'gasto_vs_ingreso_pct' => $gastoVsIngreso,
            ],
            'conteos' => [
                'viaticos' => $ruta->viaticos_count ?? 0,
                'combustibles' => $ruta->combustibles_count ?? 0,
                'peajes' => $ruta->peajes_count ?? 0,
            ],
            'analisis' => [
                'clasificacion' => $this->clasificarResultado($ingresos, $utilidad),
            ],
        ];
    }

    private function transformRutaDetalle(Ruta $ruta): array
    {
        $resumen = $this->transformRutaResumen($ruta);

        $gastos = (float) ($resumen['totales']['gastos'] ?? 0);
        $ingresos = (float) ($resumen['totales']['ingresos'] ?? 0);
        $utilidadReal = (float) ($resumen['totales']['utilidad'] ?? 0);
        $cajaChica = (float) ($ruta->caja_chica ?? 0);
        $gananciaRegistrada = (float) ($ruta->ganancia_viaje ?? 0);

        $resumen['pago_viaje'] = (float) ($ruta->pago_viaje ?? 0);
        $resumen['caja_chica'] = (float) ($ruta->caja_chica ?? 0);
        $resumen['ganancia_viaje'] = (float) ($ruta->ganancia_viaje ?? 0);
        $resumen['observaciones'] = $ruta->observaciones;
        $resumen['analisis'] = [
            'clasificacion' => $this->clasificarResultado($ingresos, $utilidadReal),
            'ingresos' => round($ingresos, 2),
            'gastos' => round($gastos, 2),
            'utilidad_real' => round($utilidadReal, 2),
            'utilidad_registrada' => round($gananciaRegistrada, 2),
            'desviacion_utilidad' => round($gananciaRegistrada - $utilidadReal, 2),
            'margen_pct' => $resumen['totales']['margen_pct'],
            'gasto_vs_ingreso_pct' => $resumen['totales']['gasto_vs_ingreso_pct'],
            'caja_chica' => round($cajaChica, 2),
            'saldo_caja_chica' => round($cajaChica - $gastos, 2),
        ];
        $resumen['viaticos'] = $ruta->viaticos->map(fn ($viatico) => [
            'id' => $viatico->id,
            'nombre_servicio' => $viatico->nombre_servicio,
            'fecha' => optional($viatico->fecha)->format('Y-m-d') ?: $viatico->fecha,
            'numero_factura' => $viatico->numero_factura,
            'importe' => (float) $viatico->importe,
            'descripcion' => $viatico->descripcion,
        ])->values();
        $resumen['combustibles'] = $ruta->combustibles->map(fn ($combustible) => [
            'id' => $combustible->id,
            'num_factura' => $combustible->num_factura,
            'grifo' => $combustible->grifo,
            'fecha_hora' => optional($combustible->fecha_hora)->format('Y-m-d H:i') ?: $combustible->fecha_hora,
            'galones' => (float) $combustible->galonesCombustible,
            'importe' => (float) $combustible->importe,
            'kilometraje_inicial' => $combustible->kilometraje_inicial,
            'kilometraje_final' => $combustible->kilometraje_final,
            'tipo_combustible' => $combustible->tipo_combustible,
        ])->values();
        $resumen['peajes'] = $ruta->peajes->map(fn ($peaje) => [
            'id' => $peaje->id,
            'nombre' => $peaje->nombre,
            'fecha_hora' => optional($peaje->fecha_hora)->format('Y-m-d H:i') ?: $peaje->fecha_hora,
            'importe' => (float) $peaje->importe,
            'comprobante' => $peaje->comprobante,
        ])->values();
        $resumen['viaticos_resumen'] = $ruta->viaticos
            ->groupBy(fn ($viatico) => trim((string) $viatico->nombre_servicio) ?: 'Sin categoria')
            ->map(function ($items, $servicio) {
                return [
                    'servicio' => $servicio,
                    'cantidad' => $items->count(),
                    'total' => round((float) $items->sum('importe'), 2),
                    'promedio' => round((float) $items->avg('importe'), 2),
                ];
            })
            ->sortByDesc('total')
            ->values();
        $totalGalones = (float) $ruta->combustibles->sum('galonesCombustible');
        $resumen['combustible_metricas'] = [
            'total_galones' => round($totalGalones, 2),
            'costo_promedio_galon' => $totalGalones > 0
                ? round(((float) $resumen['totales']['combustible']) / $totalGalones, 2)
                : 0.0,
        ];

        return $resumen;
    }

    private function clasificarResultado(float $ingresos, float $utilidad): string
    {
        if ($ingresos <= 0.009) {
            return 'sin ingreso';
        }

        if ($utilidad > 0.009) {
            return 'rentable';
        }

        if ($utilidad < -0.009) {
            return 'perdida';
        }

        return 'equilibrio';
    }
}
