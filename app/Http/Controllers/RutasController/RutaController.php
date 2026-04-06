<?php

namespace App\Http\Controllers\RutasController;

use App\Http\Controllers\Controller;
use App\Http\Requests\RutaRequest;
use App\Models\Conductor;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RutaController extends Controller
{
    public function index(Request $request)
    {
        $query = Ruta::with(['conductor.camion', 'camion']);

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

        $rutas = $query->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Listado de rutas',
            'data' => $rutas->items(),
            'pagination' => [
                'total' => $rutas->total(),
                'per_page' => $rutas->perPage(),
                'current_page' => $rutas->currentPage(),
                'last_page' => $rutas->lastPage(),
            ],
        ]);
    }

    public function store(RutaRequest $request)
    {
        $data = $request->validated();
        $data['camion_id'] = $this->resolveCamionIdFromConductor($data['conductor_id']);

        $ruta = Ruta::create($data);

        return response()->json([
            'success' => true,
            'message' => "Ruta creada: {$ruta->origen} -> {$ruta->destino}",
            'data' => $ruta->fresh(['conductor.camion', 'camion']),
        ], 201);
    }

    public function show($id)
    {
        $ruta = Ruta::with(['conductor.camion', 'camion'])->find($id);

        if (!$ruta) {
            throw ValidationException::withMessages([
                'ruta' => ['Ruta no encontrada'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Ruta obtenida: {$ruta->origen} -> {$ruta->destino}",
            'data' => $ruta,
        ]);
    }

    public function update(RutaRequest $request, $id)
    {
        $ruta = Ruta::find($id);

        if (!$ruta) {
            throw ValidationException::withMessages([
                'ruta' => ['Ruta no encontrada'],
            ]);
        }

        $data = $request->validated();
        $data['camion_id'] = $this->resolveCamionIdFromConductor($data['conductor_id']);

        $ruta->update($data);

        return response()->json([
            'success' => true,
            'message' => "Ruta actualizada: {$ruta->origen} -> {$ruta->destino}",
            'data' => $ruta->fresh(['conductor.camion', 'camion']),
        ]);
    }

    public function destroy($id)
    {
        $ruta = Ruta::find($id);

        if (!$ruta) {
            throw ValidationException::withMessages([
                'ruta' => ['Ruta no encontrada'],
            ]);
        }

        $ruta->delete();

        return response()->json([
            'success' => true,
            'message' => "Ruta eliminada: {$ruta->origen} -> {$ruta->destino}",
        ]);
    }

    private function resolveCamionIdFromConductor(int $conductorId): int
    {
        $conductor = Conductor::with('camion')->find($conductorId);

        if (!$conductor) {
            throw ValidationException::withMessages([
                'conductor_id' => ['El conductor seleccionado no existe.'],
            ]);
        }

        if (!$conductor->camion_id || !$conductor->camion) {
            throw ValidationException::withMessages([
                'conductor_id' => ['El conductor debe tener un tracto y trailer asignados.'],
            ]);
        }

        return (int) $conductor->camion_id;
    }
}
