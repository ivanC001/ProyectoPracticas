<?php

namespace App\Http\Controllers\ProductosController;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServicioRequest;
use Illuminate\Validation\ValidationException;
use App\Models\ProductosModel\Servicio;

class ServicioController extends Controller
{
    /**
     * 🔥 LISTAR (PAGINADO + BUSCADOR)
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Servicio::query()->where('activo', true);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->search . '%')
                  ->orWhere('codigo', 'like', '%' . $request->search . '%');
            });
        }

        $servicios = $query->orderBy('id', 'desc')
                           ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Lista de servicios',
            'data' => $servicios->items(),
            'pagination' => [
                'current_page' => $servicios->currentPage(),
                'last_page' => $servicios->lastPage(),
                'per_page' => $servicios->perPage(),
                'total' => $servicios->total()
            ]
        ]);
    }

    /**
     * 🔥 CREAR
     */
    public function store(ServicioRequest $request)
    {
        $data = $request->validated();

        // 🔥 GENERAR CÓDIGO SI NO VIENE
        if (empty($data['codigo'])) {
            $ultimo = Servicio::orderBy('id', 'desc')->first();

            if ($ultimo && $ultimo->codigo) {
                $numero = (int) substr($ultimo->codigo, -6) + 1;
            } else {
                $numero = 1;
            }

            $data['codigo'] = 'SERV-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        }

        // 🔥 Defaults
        $data['requiere_personal'] = $data['requiere_personal'] ?? false;
        $data['requiere_equipo'] = $data['requiere_equipo'] ?? false;
        $data['requiere_transporte'] = $data['requiere_transporte'] ?? false;
        $data['activo'] = true;

        $servicio = Servicio::create($data);

        return response()->json([
            'success' => true,
            'message' => "Servicio creado: {$servicio->nombre}",
            'data' => $servicio
        ], 201);
    }

    /**
     * 🔥 MOSTRAR
     */
    public function show($id)
    {
        $servicio = Servicio::find($id);

        if (!$servicio) {
            throw ValidationException::withMessages([
                'servicio' => ['Servicio no encontrado']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Servicio encontrado',
            'data' => $servicio
        ]);
    }

    /**
     * 🔥 ACTUALIZAR
     */
    public function update(ServicioRequest $request, $id)
    {
        $servicio = Servicio::find($id);

        if (!$servicio) {
            throw ValidationException::withMessages([
                'servicio' => ['Servicio no encontrado']
            ]);
        }

        $data = $request->validated();

        // 🔥 Defaults (por si no vienen)
        $data['requiere_personal'] = $data['requiere_personal'] ?? false;
        $data['requiere_equipo'] = $data['requiere_equipo'] ?? false;
        $data['requiere_transporte'] = $data['requiere_transporte'] ?? false;

        $servicio->update($data);

        return response()->json([
            'success' => true,
            'message' => "Servicio actualizado: {$servicio->nombre}",
            'data' => $servicio
        ]);
    }

    /**
     * 🔥 ELIMINAR (LÓGICO)
     */
    public function destroy($id)
    {
        $servicio = Servicio::find($id);

        if (!$servicio) {
            throw ValidationException::withMessages([
                'servicio' => ['Servicio no encontrado']
            ]);
        }

        $servicio->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => "Servicio eliminado: {$servicio->nombre}"
        ]);
    }
}