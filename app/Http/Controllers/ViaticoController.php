<?php

namespace App\Http\Controllers;

use App\Models\Viatico;
use App\Http\Requests\ViaticoRequest;
use Illuminate\Http\Request;

class ViaticoController extends Controller
{
    public function index()
    {
        $viaticos = Viatico::with('ruta')
                    ->withoutTrashed()
                    ->get();

        return response()->json($viaticos, 200); // 200 OK
    }

    public function store(ViaticoRequest $request)
    {
        $validatedData = $request->validated();

        $viatico = Viatico::create($validatedData);
        return response()->json($viatico, 201);
    }

    public function show($id)
    {
        $viatico = Viatico::with('ruta')->withoutTrashed()->find($id);

        if (!$viatico) {
            return response()->json(['message' => 'Viático no encontrado'], 404);
        }

        return response()->json($viatico, 200);
    }

    public function update(ViaticoRequest $request, $id)
    {
        $viatico = Viatico::find($id);

        if (!$viatico) {
            return response()->json(['message' => 'Viático no encontrado'], 404);
        }

        $validatedData = $request->validated();
        $viatico->update($validatedData);

        return response()->json($viatico, 200);
    }

    public function destroy($id)
    {
        $viatico = Viatico::find($id);

        if (!$viatico) {
            return response()->json(['message' => 'Viático no encontrado'], 404);
        }

        $viatico->delete();

        return response()->json(['message' => 'Viático eliminado'], 204);
    }
}
