<?php

namespace App\Http\Controllers;

use App\Models\Combustible;
use App\Http\Requests\CombustibleRequest; // Usar una request para validaciones
use Illuminate\Http\Request;

class CombustibleController extends Controller
{
    // Listar todos los registros de combustible (solo los no eliminados)
    public function index()
    {
        $combustibles = Combustible::with('ruta')->withoutTrashed()->get();
        return response()->json($combustibles);
    }

    // Crear un nuevo registro de combustible
    public function store(CombustibleRequest $request)
    {
        $validatedData = $request->validated();
        $combustible = Combustible::create($validatedData);
        return response()->json($combustible, 201);
    }

    

    // Mostrar un registro específico de combustible
    public function show($id)
    {
        $combustible = Combustible::with('ruta')->find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro de combustible no encontrado'], 404);
        }

        return response()->json($combustible);
    }

    // Actualizar un registro de combustible
    public function update(CombustibleRequest $request, $id)
    {
        $combustible = Combustible::find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro de combustible no encontrado'], 404);
        }

        $validatedData = $request->validated();
        $combustible->update($validatedData);

        return response()->json($combustible, 200);
    }

    // Eliminar un registro de combustible (soft delete)
    public function destroy($id)
    {
        $combustible = Combustible::find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro de combustible no encontrado'], 404);
        }

        $combustible->delete();
        return response()->json(['message' => 'Registro de combustible eliminado'], 204);
    }
}
