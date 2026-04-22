<?php

namespace App\Http\Controllers\RutasController;

use App\Http\Controllers\Controller;
use App\Http\Requests\CombustibleRequest;
use App\Models\Combustible;

class RutaCombustibleController extends Controller
{
    public function index($ruta_id)
    {
        $combustibles = Combustible::where('ruta_id', $ruta_id)
            ->orderByDesc('fecha_hora')
            ->get();

        return response()->json($combustibles, 200);
    }

    public function store(CombustibleRequest $request, $ruta_id)
    {
        $validatedData = $request->validated();
        $validatedData['ruta_id'] = $ruta_id;

        $combustible = Combustible::create($validatedData);

        return response()->json([
            'message' => 'Registro exitoso',
            'id' => $combustible->id,
            'grifo' => $combustible->grifo,
            'data' => $combustible,
        ], 201);
    }

    public function show($ruta_id, $id)
    {
        $combustible = Combustible::where('ruta_id', $ruta_id)->find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro no encontrado en esta ruta'], 404);
        }

        return response()->json($combustible, 200);
    }

    public function update(CombustibleRequest $request, $ruta_id, $id)
    {
        $combustible = Combustible::where('ruta_id', $ruta_id)->find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro no encontrado en esta ruta'], 404);
        }

        $validatedData = $request->validated();
        $validatedData['ruta_id'] = $ruta_id;
        $combustible->update($validatedData);

        return response()->json([
            'message' => 'Registro actualizado correctamente',
            'id' => $combustible->id,
            'grifo' => $combustible->grifo,
            'data' => $combustible,
        ], 200);
    }

    public function destroy($ruta_id, $id)
    {
        $combustible = Combustible::where('ruta_id', $ruta_id)->find($id);

        if (!$combustible) {
            return response()->json(['message' => 'Registro no encontrado en esta ruta'], 404);
        }

        $combustible->delete();

        return response()->json(['message' => 'Registro eliminado correctamente'], 200);
    }
}
