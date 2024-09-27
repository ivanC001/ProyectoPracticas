<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use App\Models\Camion;
use App\Models\Conductor;
use App\Http\Requests\RutaRequest;  // Importar el request de validación
use Illuminate\Http\Request;

class RutaController extends Controller
{
    // Obtener todas las rutas
    public function index()
{
    $rutas = Ruta::whereNull('deleted_at')->get(); // Solo rutas no eliminadas

    $conductores = Conductor::whereNull('deleted_at')->get();

    $camiones = Camion::whereNull('deleted_at')->get();

    return response()->json([
        'rutas' => $rutas,             // Solo rutas activas
        'conductores' => $conductores, // Conductores no eliminados
        'camiones' => $camiones        // Camiones no eliminados
    ]);
}





    public function store(RutaRequest $request)
    {
        $validatedData = $request->validated();  // Obtener los datos validados
        $ruta = Ruta::create($validatedData);


        return response()->json($ruta, 201);
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
