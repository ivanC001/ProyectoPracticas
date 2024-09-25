<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConductorController extends Controller
{
    // Obtener todos los conductores
    public function index()
    {
        $conductores = Conductor::whereNull('deleted_at')->get();
        return response()->json($conductores); 
    }

    public function deleted()
    {
        $conductoresEliminados = Conductor::onlyTrashed()->get();
        return response()->json($conductoresEliminados);
    }

    public function store(Request $request)
    {
        $conductor = Conductor::create($request->all());
        $conductor->updated_at = null;
        $conductor->save();
    
        return response()->json($conductor, 201);
        return response()->json($conductor, 201);
    }

    public function show($id)
    {
        $conductor = Conductor::find($id);

        if (!$conductor) {
            return response()->json(['message' => 'Conductor no encontrado'], 404);
        }

        return response()->json($conductor);
    }

    public function update(Request $request, $id)
    {
        $conductor = Conductor::find($id);

        if (!$conductor) {
            return response()->json(['message' => 'Conductor no encontrado'], 404);
        }

        $conductor->update($request->all());
        return response()->json($conductor, 200);
    }

    public function destroy($id)
    {
        $conductor = Conductor::find($id);

        if (!$conductor) {
            return response()->json(['message' => 'Conductor no encontrado'], 404);
        }

        $conductor->delete();
        return response()->json(['message' => 'Conductor eliminado'], 204);
    }

    public function restore($id)
    {
        $conductor = Conductor::withTrashed()->find($id);

        if (!$conductor || !$conductor->trashed()) {
            return response()->json(['message' => 'Conductor no encontrado o no está eliminado'], 404);
        }

        $conductor->restore();
        return response()->json(['message' => 'Conductor restaurado'], 200);
    }
}

