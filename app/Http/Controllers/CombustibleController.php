<?php

namespace App\Http\Controllers;

use App\Http\Requests\CombustibleRequest;
use App\Models\Combustible;

class CombustibleController extends Controller
{
    public function index()
    {
        $combustibles = Combustible::with('ruta')
            ->orderByDesc('fecha_hora')
            ->get();

        return response()->json($combustibles, 200);
    }

    public function store(CombustibleRequest $request)
    {
        $combustible = Combustible::create($request->validated());
        return response()->json($combustible, 201);
    }

    public function show($id)
    {
        $combustible = Combustible::with('ruta')->find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro de combustible no encontrado'], 404);
        }

        return response()->json($combustible, 200);
    }

    public function update(CombustibleRequest $request, $id)
    {
        $combustible = Combustible::find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro de combustible no encontrado'], 404);
        }

        $combustible->update($request->validated());
        return response()->json($combustible, 200);
    }

    public function destroy($id)
    {
        $combustible = Combustible::find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro de combustible no encontrado'], 404);
        }

        $combustible->delete();
        return response()->json(['message' => 'Registro de combustible eliminado'], 200);
    }
}
