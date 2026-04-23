<?php

namespace App\Http\Controllers\ProductosController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductosModel\Producto;
use App\Http\Requests\ProductoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductoController extends Controller
{

    /**
     * LISTAR PRODUCTOS (PAGINADO)
     */
    public function index(Request $request)
    {
        $query = Producto::query();

        // 🔍 BUSCADOR
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%$search%")
                  ->orWhere('descripcion', 'like', "%$search%")
                  ->orWhere('categoria', 'like', "%$search%");
            });
        }

        // 🔥 SOLO ACTIVOS
        if (!$request->filled('ver_inactivos')) {
            $query->where('activo', 1);
        }

        // 🔥 PAGINACIÓN + ORDEN
        $productos = $query->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Listado de productos',
            'data' => $productos->items(),
            'pagination' => [
                'total' => $productos->total(),
                'per_page' => $productos->perPage(),
                'current_page' => $productos->currentPage(),
                'last_page' => $productos->lastPage()
            ]
        ]);
    }

    /**
     * CREAR PRODUCTO
     */
    public function store(ProductoRequest $request)
    {
        DB::beginTransaction();

        try {

            $ultimo = Producto::orderBy('id', 'desc')->first();
            $numero = $ultimo ? (int) substr($ultimo->codigo, -6) + 1 : 1;

            $codigo = 'PROD-' . str_pad($numero, 6, '0', STR_PAD_LEFT);

            $producto = Producto::create([
                'codigo' => $codigo,
                'descripcion' => $request->descripcion,
                'categoria' => $request->categoria,
                'unidad' => $request->unidad ?? 'NIU',
                'precio' => $request->precio,
                'moneda_precio' => $request->moneda_precio ?? 'PEN',
                'stock' => $request->stock,
                'activo' => 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Producto creado: {$producto->descripcion} - {$producto->codigo}",
                'data' => $producto
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al crear producto'
            ], 500);
        }
    }

    /**
     * MOSTRAR PRODUCTO
     */
    public function show($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            throw ValidationException::withMessages([
                'producto' => ['Producto no encontrado']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Producto obtenido: {$producto->descripcion} - {$producto->codigo}",
            'data' => $producto
        ]);
    }

    /**
     * ACTUALIZAR PRODUCTO
     */
    public function update(ProductoRequest $request, $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            throw ValidationException::withMessages([
                'producto' => ['Producto no encontrado']
            ]);
        }

        $producto->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Producto actualizado: {$producto->descripcion} - {$producto->codigo}",
            'data' => $producto
        ]);
    }

    /**
     * ELIMINAR PRODUCTO (LÓGICO)
     */
    public function destroy($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            throw ValidationException::withMessages([
                'producto' => ['Producto no encontrado']
            ]);
        }

        $producto->update(['activo' => 0]);

        return response()->json([
            'success' => true,
            'message' => "Producto eliminado: {$producto->descripcion} - {$producto->codigo}"
        ]);
    }
}
