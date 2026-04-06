<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConductorRequest;
use App\Models\Conductor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConductorController extends Controller
{
    public function index(Request $request)
    {
        $query = Conductor::with('camion:id,placa_tracto,placa_carreto,color,mtc');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido', 'like', "%{$search}%")
                    ->orWhere('licencia', 'like', "%{$search}%")
                    ->orWhereHas('camion', function ($camionQuery) use ($search) {
                        $camionQuery->where('placa_tracto', 'like', "%{$search}%")
                            ->orWhere('placa_carreto', 'like', "%{$search}%");
                    });
            });
        }

        $conductores = $query->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Listado de conductores',
            'data' => $conductores->items(),
            'pagination' => [
                'total' => $conductores->total(),
                'per_page' => $conductores->perPage(),
                'current_page' => $conductores->currentPage(),
                'last_page' => $conductores->lastPage(),
            ],
        ]);
    }

    public function deleted()
    {
        $conductores = Conductor::onlyTrashed()
            ->with('camion:id,placa_tracto,placa_carreto')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Conductores eliminados',
            'data' => $conductores,
        ]);
    }

    public function store(ConductorRequest $request)
    {
        $conductor = Conductor::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Conductor registrado: {$conductor->nombre} {$conductor->apellido}",
            'data' => $conductor->load('camion:id,placa_tracto,placa_carreto,color,mtc'),
        ], 201);
    }

    public function show($id)
    {
        $conductor = Conductor::with('camion:id,placa_tracto,placa_carreto,color,mtc')->find($id);

        if (!$conductor) {
            throw ValidationException::withMessages([
                'conductor' => ['Conductor no encontrado'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conductor encontrado',
            'data' => $conductor,
        ]);
    }

    public function update(ConductorRequest $request, $id)
    {
        $conductor = Conductor::find($id);

        if (!$conductor) {
            throw ValidationException::withMessages([
                'conductor' => ['Conductor no encontrado'],
            ]);
        }

        $conductor->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Conductor actualizado: {$conductor->nombre} {$conductor->apellido}",
            'data' => $conductor->fresh('camion:id,placa_tracto,placa_carreto,color,mtc'),
        ]);
    }

    public function destroy($id)
    {
        $conductor = Conductor::find($id);

        if (!$conductor) {
            throw ValidationException::withMessages([
                'conductor' => ['Conductor no encontrado'],
            ]);
        }

        $conductor->delete();

        return response()->json([
            'success' => true,
            'message' => "Conductor eliminado: {$conductor->nombre} {$conductor->apellido}",
        ]);
    }

    public function restore($id)
    {
        $conductor = Conductor::withTrashed()->find($id);

        if (!$conductor || !$conductor->trashed()) {
            throw ValidationException::withMessages([
                'conductor' => ['Conductor no encontrado o no esta eliminado'],
            ]);
        }

        $conductor->restore();

        return response()->json([
            'success' => true,
            'message' => "Conductor restaurado: {$conductor->nombre} {$conductor->apellido}",
            'data' => $conductor->fresh('camion:id,placa_tracto,placa_carreto,color,mtc'),
        ]);
    }
}
