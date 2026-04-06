<?php

namespace App\Http\Controllers;

use App\Http\Requests\CamionRequest;
use App\Models\Camion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CamionController extends Controller
{
    public function index(Request $request)
    {
        $query = Camion::with(['seguros' => function ($q) {
            $q->where('activo', true)->orderBy('fecha_vencimiento');
        }])->withCount('conductores');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('placa_tracto', 'like', "%{$search}%")
                    ->orWhere('placa_carreto', 'like', "%{$search}%")
                    ->orWhere('mtc', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhereHas('seguros', function ($seguroQuery) use ($search) {
                        $seguroQuery->where('tipo_seguro', 'like', "%{$search}%")
                            ->orWhere('aseguradora', 'like', "%{$search}%")
                            ->orWhere('numero_poliza', 'like', "%{$search}%");
                    });
            });
        }

        $camiones = $query->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        $data = collect($camiones->items())
            ->map(fn ($camion) => $this->appendSeguroResumen($camion))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Listado de tractos y trailers',
            'data' => $data,
            'pagination' => [
                'total' => $camiones->total(),
                'per_page' => $camiones->perPage(),
                'current_page' => $camiones->currentPage(),
                'last_page' => $camiones->lastPage(),
            ],
        ]);
    }

    public function deleted()
    {
        $camiones = Camion::onlyTrashed()
            ->withCount('conductores')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Unidades eliminadas',
            'data' => $camiones,
        ]);
    }

    public function store(CamionRequest $request)
    {
        $camion = Camion::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Unidad registrada: {$camion->placa_tracto} / {$camion->placa_carreto}",
            'data' => $this->appendSeguroResumen(
                $camion->fresh()->load(['seguros', 'conductores'])->loadCount('conductores')
            ),
        ], 201);
    }

    public function show($id)
    {
        $camion = Camion::with(['seguros' => function ($q) {
            $q->orderBy('fecha_vencimiento');
        }])->withCount('conductores')->find($id);

        if (!$camion) {
            throw ValidationException::withMessages([
                'camion' => ['Unidad no encontrada'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Unidad encontrada',
            'data' => $this->appendSeguroResumen($camion),
        ]);
    }

    public function update(CamionRequest $request, $id)
    {
        $camion = Camion::find($id);

        if (!$camion) {
            throw ValidationException::withMessages([
                'camion' => ['Unidad no encontrada'],
            ]);
        }

        $camion->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Unidad actualizada: {$camion->placa_tracto} / {$camion->placa_carreto}",
            'data' => $this->appendSeguroResumen(
                $camion->fresh()->load(['seguros', 'conductores'])->loadCount('conductores')
            ),
        ]);
    }

    public function destroy($id)
    {
        $camion = Camion::find($id);

        if (!$camion) {
            throw ValidationException::withMessages([
                'camion' => ['Unidad no encontrada'],
            ]);
        }

        $camion->delete();

        return response()->json([
            'success' => true,
            'message' => "Unidad eliminada: {$camion->placa_tracto} / {$camion->placa_carreto}",
        ]);
    }

    public function restore($id)
    {
        $camion = Camion::withTrashed()->find($id);

        if (!$camion || !$camion->trashed()) {
            throw ValidationException::withMessages([
                'camion' => ['Unidad no encontrada o no esta eliminada'],
            ]);
        }

        $camion->restore();

        return response()->json([
            'success' => true,
            'message' => "Unidad restaurada: {$camion->placa_tracto} / {$camion->placa_carreto}",
            'data' => $this->appendSeguroResumen(
                $camion->fresh()->load(['seguros', 'conductores'])->loadCount('conductores')
            ),
        ]);
    }

    private function appendSeguroResumen(Camion $camion): Camion
    {
        $seguros = ($camion->seguros instanceof Collection ? $camion->seguros : collect($camion->seguros ?? []))
            ->where('activo', true)
            ->sortBy('fecha_vencimiento')
            ->values();

        $hoy = now()->startOfDay();
        $proximoSeguro = $seguros->first();

        $camion->seguros_vencidos_count = $seguros->filter(function ($seguro) use ($hoy) {
            return $seguro->fecha_vencimiento && $seguro->fecha_vencimiento->copy()->startOfDay()->lt($hoy);
        })->count();

        $camion->seguros_por_vencer_count = $seguros->filter(function ($seguro) use ($hoy) {
            if (!$seguro->fecha_vencimiento) {
                return false;
            }

            $dias = $hoy->diffInDays($seguro->fecha_vencimiento->copy()->startOfDay(), false);
            return $dias >= 0 && $dias <= (int) $seguro->alertar_dias_antes;
        })->count();

        $camion->proximo_seguro = $proximoSeguro
            ? [
                'id' => $proximoSeguro->id,
                'tipo_seguro' => $proximoSeguro->tipo_seguro,
                'fecha_vencimiento' => optional($proximoSeguro->fecha_vencimiento)->toDateString(),
                'dias_restantes' => $hoy->diffInDays($proximoSeguro->fecha_vencimiento->copy()->startOfDay(), false),
            ]
            : null;

        return $camion;
    }
}
