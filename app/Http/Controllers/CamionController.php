<?php
namespace App\Http\Controllers;

use App\Models\Camion;
use Illuminate\Http\Request;

class CamionController extends Controller
{
    // Obtener todos los camiones
    public function index()
    {
        $camiones = Camion::whereNull('deleted_at')->get();  // Lista solo los no eliminados
        return response()->json($camiones); 
    }

    // Obtener todos los camiones eliminados
    public function deleted()
    {
        $camionesEliminados = Camion::onlyTrashed()->get();  // Lista los eliminados
        return response()->json($camionesEliminados);
    }

    // Crear un nuevo camión
    public function store(Request $request)
    {
        $camion = Camion::create($request->all());
        $camion->updated_at = null;  // Puedes resetear el campo updated_at si es necesario
        $camion->save();

        return response()->json($camion, 201);
    }

    // Mostrar un camión específico
    public function show($id)
    {
        $camion = Camion::find($id);

        if (!$camion) {
            return response()->json(['message' => 'Camión no encontrado'], 404);
        }

        return response()->json($camion);
    }

    // Actualizar un camión específico
    public function update(Request $request, $id)
    {
        $camion = Camion::find($id);

        if (!$camion) {
            return response()->json(['message' => 'Camión no encontrado'], 404);
        }

        $camion->update($request->all());
        return response()->json($camion, 200);
    }

    // Eliminar un camión (soft delete)
    public function destroy($id)
    {
        $camion = Camion::find($id);

        if (!$camion) {
            return response()->json(['message' => 'Camión no encontrado'], 404);
        }

        $camion->delete();
        return response()->json(['message' => 'Camión eliminado'], 204);
    }

    // Restaurar un camión eliminado
    public function restore($id)
    {
        $camion = Camion::withTrashed()->find($id);

        if (!$camion || !$camion->trashed()) {
            return response()->json(['message' => 'Camión no encontrado o no está eliminado'], 404);
        }

        $camion->restore();
        return response()->json(['message' => 'Camión restaurado'], 200);
    }
}
