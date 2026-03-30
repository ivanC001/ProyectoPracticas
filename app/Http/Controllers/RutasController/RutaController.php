<?php

namespace App\Http\Controllers\RutasController;

use App\Models\Ruta;
use App\Http\Requests\RutaRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class RutaController extends Controller
{
    /**
     * LISTAR (PAGINADO + BUSCADOR)
     */
    public function index(Request $request)
    {
        $query = Ruta::with(['conductor', 'camion']);

        // 🔍 BUSCADOR GLOBAL
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('origen', 'like', "%$search%")
                  ->orWhere('destino', 'like', "%$search%");
            });
        }

        // 🔥 ORDEN + PAGINACIÓN
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
                'last_page' => $rutas->lastPage()
            ]
        ]);
    }

    /**
     * CREAR
     */
    public function store(RutaRequest $request)
    {
        $ruta = Ruta::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Ruta creada: {$ruta->origen} → {$ruta->destino}",
            'data' => $ruta
        ], 201);
    }

    /**
     * MOSTRAR
     */
    public function show($id)
    {
        $ruta = Ruta::with(['conductor', 'camion'])->find($id);

        if (!$ruta) {
            throw ValidationException::withMessages([
                'ruta' => ['Ruta no encontrada']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Ruta obtenida: {$ruta->origen} → {$ruta->destino}",
            'data' => $ruta
        ]);
    }

    /**
     * ACTUALIZAR
     */
    public function update(RutaRequest $request, $id)
    {
        $ruta = Ruta::find($id);

        if (!$ruta) {
            throw ValidationException::withMessages([
                'ruta' => ['Ruta no encontrada']
            ]);
        }

        $ruta->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Ruta actualizada: {$ruta->origen} → {$ruta->destino}",
            'data' => $ruta
        ]);
    }

    /**
     * ELIMINAR (RECOMENDADO: LÓGICO)
     */
    public function destroy($id)
    {
        $ruta = Ruta::find($id);

        if (!$ruta) {
            throw ValidationException::withMessages([
                'ruta' => ['Ruta no encontrada']
            ]);
        }

        // 🔥 OPCIÓN 1: eliminación lógica
        // $ruta->update(['estado' => false]);

        // 🔥 OPCIÓN 2: eliminación física
        $ruta->delete();

        return response()->json([
            'success' => true,
            'message' => "Ruta eliminada: {$ruta->origen} → {$ruta->destino}"
        ]);
    }
}