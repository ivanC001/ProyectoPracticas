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

    // Registrar un nuevo conductor
    public function store(Request $request)
    {
        // Validaciones
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'required|in:Masculino,Femenino',
            'licencia' => 'required|string|max:20',
            'tipo_licencia' => 'required|in:A,B,C,D,E',
            'telefono' => 'nullable|string|max:15',
            'email' => 'nullable|string|email|max:100',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Crear el conductor
        $conductor = Conductor::create($request->all());
        $conductor->updated_at = null;
        $conductor->save();
    
        return response()->json($conductor, 201);
    }

    // Mostrar un conductor específico
    public function show($id)
    {
        $conductor = Conductor::find($id);

        if (!$conductor) {
            return response()->json(['message' => 'Conductor no encontrado'], 404);
        }

        return response()->json($conductor);
    }

    // Actualizar un conductor
    public function update(Request $request, $id)
    {
        $conductor = Conductor::find($id);

        if (!$conductor) {
            return response()->json(['message' => 'Conductor no encontrado'], 404);
        }

        // Validaciones para actualización
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'required|in:Masculino,Femenino',
            'licencia' => 'required|string|max:20',
            'tipo_licencia' => 'required|in:A,B,C,D,E',
            'telefono' => 'nullable|string|max:15',
            'email' => 'nullable|string|email|max:100',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Actualizar los datos del conductor
        $conductor->update($request->all());
        return response()->json($conductor, 200);
    }

    // Eliminar (soft delete) un conductor
    public function destroy($id)
    {
        $conductor = Conductor::find($id);

        if (!$conductor) {
            return response()->json(['message' => 'Conductor no encontrado'], 404);
        }

        $conductor->delete();
        return response()->json(['message' => 'Conductor eliminado'], 204);
    }

    // Restaurar un conductor eliminado
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

