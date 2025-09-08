<?php

namespace App\Http\Controllers;

use App\Models\Combustible;
use App\Http\Requests\CombustibleRequest;

class RutaCombustibleController extends Controller
{
    // Listar combustibles de una ruta
    public function index($rutaId)
    {
        $combustibles = Combustible::where('ruta_id', $rutaId)->get();

        if ($combustibles->isEmpty()) {
            return response()->json(['message' => 'No hay registros de combustible para esta ruta'], 404);
        }

        return response()->json($combustibles, 200);
    }

    // Crear combustible en una ruta
    public function store(CombustibleRequest $request, $rutaId)
    {
        $validatedData = $request->validated();
        $validatedData['ruta_id'] = $rutaId; // forzar que pertenezca a esta ruta

        $combustible = Combustible::create($validatedData);

        return response()->json([
            'message' => 'Registro exitoso',
            'id'      => $combustible->id,
            'grifo'   => $combustible->grifo
        ], 201);
    }

    // Mostrar un combustible
    public function show($rutaId, $id)
    {
        $combustible = Combustible::where('ruta_id', $rutaId)->find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro no encontrado en esta ruta'], 404);
        }

        return response()->json($combustible, 200);
    }

    // Actualizar un combustible
    public function update(CombustibleRequest $request, $rutaId, $id)
    {
        $combustible = Combustible::where('ruta_id', $rutaId)->find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro no encontrado en esta ruta'], 404);
        }

        $combustible->update($request->validated());

        return response()->json([
            'message' => 'Registro actualizado correctamente',
            'id'      => $combustible->id,
            'grifo'   => $combustible->grifo
        ], 200);
    }

    // Eliminar un combustible
    public function destroy($rutaId, $id)
    {
        $combustible = Combustible::where('ruta_id', $rutaId)->find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro no encontrado en esta ruta'], 404);
        }

        $combustible->delete();

        return response()->json(['message' => 'Registro eliminado correctamente'], 200);
    }
}
