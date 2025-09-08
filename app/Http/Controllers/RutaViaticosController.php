<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use App\Models\Viatico;
use App\Models\Combustible;
use App\Http\Requests\RutaRequest;
use Illuminate\Http\Request;

class RutaViaticosController extends Controller
{
    /**
     * Obtener todas las rutas con conductor, camión, viáticos y combustibles
     */
    public function index()
    {
        $rutas = Ruta::with(['conductor', 'camion', 'viaticos', 'combustibles'])->get();
        return response()->json($rutas);
    }

    /**
     * Crear una nueva ruta
     */
    public function store(RutaRequest $request)
    {
        $validatedData = $request->validated();
        $ruta = Ruta::create($validatedData);

        return response()->json($ruta, 201);
    }

    /**
     * Mostrar una ruta específica con todos sus datos relacionados
     */
    public function show($id)
    {
        $ruta = Ruta::with(['conductor', 'camion', 'viaticos', 'combustibles'])->find($id);

        if (!$ruta) {
            return response()->json(['message' => 'Ruta no encontrada'], 404);
        }

        return response()->json($ruta);
    }

    /**
     * Actualizar una ruta específica y/o sus viáticos y combustibles
     */
    public function update(Request $request, $id)
    {
        $ruta = Ruta::with(['viaticos', 'combustibles'])->find($id);

        if (!$ruta) {
            return response()->json(['message' => 'Ruta no encontrada'], 404);
        }

        // Validación de datos
        $data = $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'origen' => 'nullable|string|max:255',
            'destino' => 'nullable|string|max:255',
            'conductor_id' => 'nullable|exists:conductores,id',
            'camion_id' => 'nullable|exists:camiones,id',

            // Viáticos
            'viaticos' => 'nullable|array',
            'viaticos.*.id' => 'nullable|exists:viaticos,id',
            'viaticos.*.nombre_servicio' => 'nullable|string|max:255',
            'viaticos.*.fecha' => 'nullable|date',
            'viaticos.*.numero_factura' => 'nullable|string|max:255',
            'viaticos.*.importe' => 'nullable|numeric',
            'viaticos.*.descripcion' => 'nullable|string',

            // Combustibles
            'combustibles' => 'nullable|array',
            'combustibles.*.id' => 'nullable|exists:combustibles,id',
            'combustibles.*.num_factura' => 'nullable|string|max:255',
            'combustibles.*.grifo' => 'nullable|string|max:255',
            'combustibles.*.fecha_hora' => 'nullable|date',
            'combustibles.*.galonesCombustible' => 'nullable|numeric',
            'combustibles.*.importe' => 'nullable|numeric',
            'combustibles.*.kilometraje_inicial' => 'nullable|numeric',
            'combustibles.*.kilometraje_final' => 'nullable|numeric',
            'combustibles.*.tipo_combustible' => 'nullable|string|max:100',
        ]);

        // Actualizar datos de la ruta
        $ruta->update($request->only([
            'fecha_inicio',
            'fecha_fin',
            'origen',
            'destino',
            'conductor_id',
            'camion_id'
        ]));

        // Actualizar o crear viáticos
        if (!empty($data['viaticos'])) {
            foreach ($data['viaticos'] as $viaticoData) {
                if (!empty($viaticoData['id'])) {
                    Viatico::find($viaticoData['id'])->update($viaticoData);
                } else {
                    $ruta->viaticos()->create($viaticoData);
                }
            }
        }

        // Actualizar o crear combustibles
        if (!empty($data['combustibles'])) {
            foreach ($data['combustibles'] as $combustibleData) {
                if (!empty($combustibleData['id'])) {
                    Combustible::find($combustibleData['id'])->update($combustibleData);
                } else {
                    $ruta->combustibles()->create($combustibleData);
                }
            }
        }

        return response()->json([
            'message' => 'Ruta actualizada correctamente',
            'ruta' => $ruta->fresh(['conductor', 'camion', 'viaticos', 'combustibles'])
        ]);
    }

    /**
     * Eliminar una ruta
     */
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
