<?php

namespace App\Http\Controllers\ProductosController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductosModel\Producto;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Listar productos
    |--------------------------------------------------------------------------
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

        // 📌 SOLO ACTIVOS (por defecto)
        if (!$request->filled('ver_inactivos')) {
            $query->where('activo', 1);
        }

        // 🔽 ORDEN + SELECT OPTIMIZADO
        $productos = $query->orderBy('id', 'desc')
            ->select(
                'id',
                'codigo',
                'descripcion',
                'categoria',
                'unidad',
                'precio',
                'stock',
                'activo'
            )
            ->paginate(20); // 🔥 CLAVE PRODUCCIÓN

        return response()->json($productos);
    }


    /*
    |--------------------------------------------------------------------------
    | Crear producto
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'categoria' => 'required|string|max:100',
            'precio' => 'required|numeric',
            'stock' => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {

            // 🔥 OBTENER ÚLTIMO CÓDIGO
            $ultimoProducto = Producto::orderBy('id', 'desc')->first();

            if ($ultimoProducto && $ultimoProducto->codigo) {

                // Extraer número
                $numero = (int) substr($ultimoProducto->codigo, -6);
                $nuevoNumero = $numero + 1;

            } else {

                // 🔥 SI NO EXISTE NINGUNO
                $nuevoNumero = 1;

            }

            // FORMATO: PROD-000001
            $codigo = 'PROD-' . str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);

            // 🔥 CREAR PRODUCTO
            $producto = Producto::create([
                'codigo' => $codigo,
                'descripcion' => $request->descripcion,
                'categoria' => $request->categoria,
                'unidad' => $request->unidad ?? 'NIU',
                'precio' => $request->precio,
                'stock' => $request->stock,
                'activo' => 1 // 🔥 SIEMPRE ACTIVO
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $producto
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar producto',
                'error' => $e->getMessage()
            ], 500);

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Mostrar producto
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        return response()->json(
            Producto::findOrFail($id)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar producto
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {

        $producto = Producto::findOrFail($id);

        $producto->update([

            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'categoria' => $request->categoria,
            'unidad' => $request->unidad,
            'precio' => $request->precio,
            'stock' => $request->stock

        ]);

        return response()->json($producto);

    }


    /*
    |--------------------------------------------------------------------------
    | Eliminar producto
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {

        $producto = Producto::findOrFail($id);

        $producto->update([
            'activo' => 0
        ]);

        return response()->json([
            'mensaje' => 'Producto eliminado'
        ]);

    }

}