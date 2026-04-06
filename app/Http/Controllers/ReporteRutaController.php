<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class ReporteRutaController extends Controller
{
    public function index(Request $request)
    {
        $query = Ruta::with(['conductor.camion', 'camion'])
            ->withSum('viaticos', 'importe')
            ->withSum('combustibles', 'importe')
            ->withSum('peajes', 'importe')
            ->withCount(['viaticos', 'combustibles', 'peajes']);

        if ($request->filled('search')) {
            $search = $request->search;

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

        $rutas = $query->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        $data = collect($rutas->items())
            ->map(fn ($ruta) => $this->transformRutaResumen($ruta))
            ->values();

        $kpisBase = (clone $query)->get();
        $kpis = [
            'total_rutas' => $kpisBase->count(),
            'total_viaticos' => round((float) $kpisBase->sum('viaticos_sum_importe'), 2),
            'total_combustible' => round((float) $kpisBase->sum('combustibles_sum_importe'), 2),
            'total_peajes' => round((float) $kpisBase->sum('peajes_sum_importe'), 2),
        ];
        $kpis['total_gastos'] = round($kpis['total_viaticos'] + $kpis['total_combustible'] + $kpis['total_peajes'], 2);

        return response()->json([
            'success' => true,
            'message' => 'Reporte de rutas',
            'data' => $data,
            'kpis' => $kpis,
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

    private function transformRutaResumen(Ruta $ruta): array
    {
        $viaticos = (float) ($ruta->viaticos_sum_importe ?? 0);
        $combustible = (float) ($ruta->combustibles_sum_importe ?? 0);
        $peajes = (float) ($ruta->peajes_sum_importe ?? 0);

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
                'gastos' => round($viaticos + $combustible + $peajes, 2),
            ],
            'conteos' => [
                'viaticos' => $ruta->viaticos_count ?? 0,
                'combustibles' => $ruta->combustibles_count ?? 0,
                'peajes' => $ruta->peajes_count ?? 0,
            ],
        ];
    }

    private function transformRutaDetalle(Ruta $ruta): array
    {
        $resumen = $this->transformRutaResumen($ruta);

        $resumen['pago_viaje'] = (float) ($ruta->pago_viaje ?? 0);
        $resumen['caja_chica'] = (float) ($ruta->caja_chica ?? 0);
        $resumen['ganancia_viaje'] = (float) ($ruta->ganancia_viaje ?? 0);
        $resumen['observaciones'] = $ruta->observaciones;
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

        return $resumen;
    }
}
