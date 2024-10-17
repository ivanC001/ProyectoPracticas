<?php

namespace App\Domains\Inventarios\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Inventarios\Models\Producto;
use App\Domains\Inventarios\Models\ControlProducto; // Importamos ControlProducto para crear los registros automáticos
use App\Domains\Inventarios\Requests\ProductoRequest; // Usar la única request class
use Illuminate\Http\Response;

class ProductoController extends Controller
{
    // Obtener todos los productos
    public function index()
    {
        $productos = Producto::all();
        return response()->json($productos, Response::HTTP_OK);
    }

    // Crear un nuevo producto
    public function store(ProductoRequest $request)
    {
        $validated = $request->validated();

        // Crear el producto
        $producto = Producto::create($validated);

        // Automáticamente registrar en control_productos el movimiento de entrada
        ControlProducto::create([
            'producto_id' => $producto->id,
            'tipo_accion' => 'entrada', // Indica que es una entrada de stock
            'cantidad' => $validated['cantidad_stock'], // Registramos la cantidad del stock inicial
            'descripcion' => 'Stock inicial al crear el producto'
        ]);

        return response()->json($producto, Response::HTTP_CREATED);
    }

    // Mostrar un producto específico
    public function show($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($producto, Response::HTTP_OK);
    }

    // Actualizar un producto
    public function update(ProductoRequest $request, $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validated();

        // Si el stock cambió, registrar una entrada o salida automática en control_productos
        if (isset($validated['cantidad_stock']) && $validated['cantidad_stock'] != $producto->cantidad_stock) {
            // Diferencia entre el stock anterior y el nuevo
            $diferencia = $validated['cantidad_stock'] - $producto->cantidad_stock;
            $tipo_accion = $diferencia > 0 ? 'entrada' : 'salida'; // Entrada o salida según la diferencia

            ControlProducto::create([
                'producto_id' => $producto->id,
                'tipo_accion' => $tipo_accion,
                'cantidad' => abs($diferencia),  // Guardar la diferencia en positivo
                'descripcion' => 'Actualización de stock' // Descripción opcional
            ]);
        }

        // Actualizar el producto
        $producto->update($validated);

        return response()->json($producto, Response::HTTP_OK);
    }

    // Eliminar un producto (soft delete)
    public function destroy($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Registrar salida total del stock antes de eliminar el producto si hay stock disponible
        if ($producto->cantidad_stock > 0) {
            ControlProducto::create([
                'producto_id' => $producto->id,
                'tipo_accion' => 'salida',  // Registrar salida total del stock
                'cantidad' => $producto->cantidad_stock,
                'descripcion' => 'Salida total antes de eliminar el producto'
            ]);
        } 

        
        $producto->delete();

        return response()->json(['message' => 'Producto eliminado correctamente'], Response::HTTP_OK);
    }
}
