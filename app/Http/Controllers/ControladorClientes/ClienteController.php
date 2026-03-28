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
     * LISTAR CLIENTES
     */
    public function index(Request $request)
    {
        $query = Cliente::query()->where('estado', true);

        if ($request->filled('num_doc')) {
            $query->where('num_doc', 'like', '%' . $request->num_doc . '%');
        }

        if ($request->filled('razon_social')) {
            $query->where('razon_social', 'like', '%' . $request->razon_social . '%');
        }

        $clientes = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de clientes',
            'data' => $clientes
        ]);
    }

    /**
     * CREAR CLIENTE
     */
    public function store(ClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Cliente Registrado: {$cliente->razon_social} - {$cliente->num_doc}",
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

        return response()->json([
            'success' => true,
            'message' => 'Cliente encontrado',
            'data' => $cliente
        ]);
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
            'success' => true,
            'message' => "Cliente Actualizado: {$cliente->razon_social} - {$cliente->num_doc}",
            'data' => $cliente
        ]);
    }

    /**
     * ELIMINAR CLIENTE (LÓGICO)
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
            'message' => "Cliente Eliminado: {$cliente->razon_social} - {$cliente->num_doc}"
        ]);
    }
}