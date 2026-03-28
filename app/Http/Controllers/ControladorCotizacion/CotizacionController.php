<?php

namespace App\Http\Controllers\ControladorCotizacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CotizacionModel\Cotizacion;
use App\Models\CotizacionModel\CotizacionDetalle;
use App\Models\ProductosModel\Producto;
use App\Models\ProductoModel\Servicio;

class CotizacionController extends Controller
{

    /* 🔥 LISTAR */
    public function index()
    {
        return response()->json(
            Cotizacion::with('cliente', 'detalles')->latest()->get()
        );
    }

    /* 🔥 REGISTRAR */
    public function store(Request $request)
    {
        try {

            // // 🔹 VALIDACIÓN
            // $request->validate([
            //     'cliente_id' => 'required|exists:clientes,id',
            //     'items' => 'required|array|min:1',

            //     'items.*.tipo' => 'required|in:producto,servicio',
            //     'items.*.cantidad' => 'required|numeric|min:1',

            //     'items.*.producto_id' => 'nullable|exists:productos,id',
            //     'items.*.servicio_id' => 'nullable|exists:servicios,id',

            //     'items.*.precio' => 'nullable|numeric|min:0'
            // ]);

            // 🔹 CREAR CABECERA
            $cotizacion = Cotizacion::create([
                'cliente_id' => $request->cliente_id,
                'fecha' => now(),
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'subtotal' => 0,
                'igv' => 0,
                'total' => 0,
                'estado' => 'borrador',
                'observacion' => $request->observacion
            ]);

            $subtotal = 0;

            foreach ($request->items as $item) {

                $tipo = $item['tipo'];
                $cantidad = $item['cantidad'];

                if ($tipo === 'producto') {

                    if (empty($item['producto_id'])) {
                        throw new \Exception('Debe enviar producto_id');
                    }

                    // 🔥 IMPORTANTE: excluir eliminados (SoftDeletes)
                    $producto = Producto::where('activo', true)
                                        ->findOrFail($item['producto_id']);

                    $precio = $producto->precio;
                    $descripcion = $producto->descripcion;

                    $producto_id = $producto->id;
                    $servicio_id = null;

                } else {

                    if (empty($item['servicio_id'])) {
                        throw new \Exception('Debe enviar servicio_id');
                    }

                    $servicio = Servicio::where('activo', true)
                                        ->findOrFail($item['servicio_id']);

                    $precio = $item['precio'] ?? $servicio->precio;
                    $descripcion = $servicio->nombre;

                    $producto_id = null;
                    $servicio_id = $servicio->id;
                }

                $sub = round($cantidad * $precio, 2);

                CotizacionDetalle::create([
                    'cotizacion_id' => $cotizacion->id,
                    'tipo' => $tipo,
                    'producto_id' => $producto_id,
                    'servicio_id' => $servicio_id,
                    'descripcion' => $descripcion,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $sub
                ]);

                $subtotal += $sub;
            }

            // 🔹 TOTALES
            $igv = round($subtotal * 0.18, 2);
            $total = round($subtotal + $igv, 2);

            $cotizacion->update([
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cotización registrada correctamente',
                'data' => $cotizacion->load('cliente', 'detalles')
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /* 🔥 MOSTRAR */
    public function show($id)
    {
        return response()->json(
            Cotizacion::with('cliente', 'detalles')->findOrFail($id)
        );
    }

    /* 🔥 ELIMINAR */
    public function destroy($id)
    {
        Cotizacion::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cotización eliminada'
        ]);
    }
}