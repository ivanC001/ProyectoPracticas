<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use App\Http\Requests\RutaRequest;  // Importar el request de validación
use Illuminate\Http\Request;

class RutaController extends Controller
{
    // Obtener todas las rutas
    public function index()
    {
        $rutas = Ruta::with(['conductor', 'camion'])->get();  // Carga las relaciones
        return response()->json($rutas); 
    }

    // Crear una nueva ruta
    public function store(RutaRequest $request)
    {
        $validatedData = $request->validated();  // Obtener los datos validados
        $ruta = Ruta::create($validatedData);


         return response()->json([
        'message' => 'Ruta registrada exitosamente',
        'ruta' => [
            'origen' => $ruta->origen,
            'destino' => $ruta->destino,
            'estado' => $ruta->estado,
            'observaciones' => $ruta->observaciones,
        ]
        ], 201);
    }

    // Mostrar una ruta específica
    public function show($id)
    {
        $ruta = Ruta::with(['conductor', 'camion'])->find($id);

        if (!$ruta) {
            return response()->json(['message' => 'Ruta no encontrada'], 404);
        }

        return response()->json($ruta);
    }

    // Actualizar una ruta específica
    public function update(RutaRequest $request, $id)
    {
        $ruta = Ruta::find($id);

        if (!$ruta) {
            return response()->json(['message' => 'Ruta no encontrada'], 404);
        }

        $validatedData = $request->validated();  // Obtener los datos validados
        $ruta->update($validatedData);
        return response()->json($ruta, 200);
    }

    // Eliminar una ruta
    public function destroy($id)
    {
        $ruta = Ruta::find($id);

        if (!$ruta) {
            return response()->json(['message' => 'Ruta no encontrada'], 404);
        }

        $ruta->delete();
        return response()->json(['message' => 'Ruta eliminada'], 204);
    }
}
