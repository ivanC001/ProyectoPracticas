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
     * LISTAR CON PAGINACIÓN + FILTRO
     */
    public function index(Request $request)
    {
        $query = Cliente::query()->where('estado', true);

        // 🔍 filtro por documento
        if ($request->filled('num_doc')) {
            $query->where('num_doc', 'like', '%' . $request->num_doc . '%');
        }

        // 🔍 filtro por nombre
        if ($request->filled('razon_social')) {
            $query->where('razon_social', 'like', '%' . $request->razon_social . '%');
        }

        // 🔥 PAGINACIÓN
        $clientes = $query->orderBy('id', 'desc')
                          ->paginate($request->get('per_page', 10));

        return response()->json($clientes);
    }

    /**
     * CREAR CLIENTE
     */
    public function store(ClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());

        return response()->json([
            'message' => 'Cliente creado correctamente',
            'data' => $cliente
        ], 201);
    }

    /**
     * MOSTRAR CLIENTE
     */
    public function show($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            throw ValidationException::withMessages([
                'cliente' => ['Cliente no encontrado']
            ]);
        }

        return response()->json($cliente);
    }

    /**
     * ACTUALIZAR CLIENTE
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
            'message' => 'Cliente actualizado',
            'data' => $cliente
        ]);
    }

    /**
     * ELIMINAR (LÓGICO)
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
            'message' => 'Cliente desactivado'
        ]);
    }
}