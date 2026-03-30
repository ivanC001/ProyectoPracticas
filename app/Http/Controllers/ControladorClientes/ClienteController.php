<?php

namespace App\Http\Controllers\ControladorClientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClientesModel\Cliente;
use App\Http\Requests\ClienteRequest;
use Illuminate\Validation\ValidationException;

class ClienteController extends Controller
{
    /**
     * LISTAR CLIENTES (PAGINADO + BUSCADOR)
     */
    public function index(Request $request)
    {
        $query = Cliente::query()->where('estado', true);

        // 🔍 BUSCADOR GLOBAL (más rápido)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('num_doc', 'like', "%$search%")
                  ->orWhere('razon_social', 'like', "%$search%");
            });
        }

        // 🔥 PAGINACIÓN + ORDEN
        $clientes = $query->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Listado de clientes',
            'data' => $clientes->items(),
            'pagination' => [
                'total' => $clientes->total(),
                'per_page' => $clientes->perPage(),
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage()
            ]
        ]);
    }

    /**
     * CREAR
     */
    public function store(ClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Cliente creado: {$cliente->razon_social} - {$cliente->num_doc}",
            'data' => $cliente
        ], 201);
    }

    /**
     * MOSTRAR
     */
    public function show($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            throw ValidationException::withMessages([
                'cliente' => ['Cliente no encontrado']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Cliente obtenido: {$cliente->razon_social}",
            'data' => $cliente
        ]);
    }

    /**
     * ACTUALIZAR
     */
    public function update(ClienteRequest $request, $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            throw ValidationException::withMessages([
                'cliente' => ['Cliente no encontrado']
            ]);
        }

        $cliente->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Cliente actualizado: {$cliente->razon_social} - {$cliente->num_doc}",
            'data' => $cliente
        ]);
    }

    /**
     * ELIMINAR
     */
    public function destroy($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            throw ValidationException::withMessages([
                'cliente' => ['Cliente no encontrado']
            ]);
        }

        $cliente->update(['estado' => false]);

        return response()->json([
            'success' => true,
            'message' => "Cliente eliminado: {$cliente->razon_social} - {$cliente->num_doc}"
        ]);
    }
}