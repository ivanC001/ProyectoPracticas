<?php

namespace App\Http\Controllers;

use App\Models\Peaje;
use Illuminate\Http\Request;

class RutaPeajeController extends Controller
{
    // Listar peajes de una ruta
    public function index($rutaId)
    {
        $peajes = Peaje::where('ruta_id', $rutaId)->get();

        if ($peajes->isEmpty()) {
            return response()->json(['message' => 'No hay registros de peajes para esta ruta'], 404);
        }

        return response()->json($peajes, 200);
    }

    // Registrar un peaje
    public function store(Request $request, $rutaId)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'importe' => 'required|numeric',
            'fecha_hora' => 'required|date',
            'comprobante' => 'nullable|string|max:255',
        ]);

        $peaje = Peaje::create([
            'ruta_id' => $rutaId,
            'nombre' => $request->nombre,
            'importe' => $request->importe,
            'fecha_hora' => $request->fecha_hora,
            'comprobante' => $request->comprobante,
        ]);

        return response()->json([
            'message' => 'Peaje registrado exitosamente',
            'id' => $peaje->id,
            'nombre' => $peaje->nombre
        ], 201);
    }

    // Mostrar un peaje
    public function show($rutaId, $id)
    {
        $peaje = Peaje::where('ruta_id', $rutaId)->find($id);

        if (!$peaje) {
            return response()->json(['message' => 'Registro no encontrado en esta ruta'], 404);
        }

        return response()->json($peaje, 200);
    }

    // Actualizar un peaje
    public function update(Request $request, $rutaId, $id)
    {
        $peaje = Peaje::where('ruta_id', $rutaId)->find($id);

        if (!$peaje) {
            return response()->json(['message' => 'Registro no encontrado en esta ruta'], 404);
        }

        $peaje->update($request->all());

        return response()->json([
            'message' => 'Peaje actualizado correctamente',
            'id' => $peaje->id,
            'nombre' => $peaje->nombre
        ], 200);
    }

    // Eliminar un peaje
    public function destroy($rutaId, $id)
    {
        $peaje = Peaje::where('ruta_id', $rutaId)->find($id);

        if (!$peaje) {
            return response()->json(['message' => 'Registro no encontrado en esta ruta'], 404);
        }

        $peaje->delete();

        return response()->json(['message' => 'Peaje eliminado correctamente'], 200);
    }
}
