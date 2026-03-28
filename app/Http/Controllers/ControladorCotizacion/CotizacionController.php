<?php

namespace App\Http\Controllers\ControladorCotizacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\CotizacionModel\Cotizacion;
use App\Models\CotizacionModel\CotizacionDetalle;

use App\Models\ProductosModel\Producto;

class CotizacionController extends Controller
{

    /* 🔥 LISTAR */
    public function index()
    {
        $data = Cotizacion::with('cliente', 'detalles.item')->latest()->get();

        return response()->json($data);
    }

    /* 🔥 GUARDAR */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            // 🔹 VALIDACIÓN COMPLETA
        
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

            // 🔹 GUARDAR DETALLE (CON REGLAS 🔥)
            foreach ($request->items as $item) {

                $itemDB = Producto::findOrFail($item['item_id']);

                // 🔥 REGLA PRINCIPAL
                if ($itemDB->tipo === 'producto') {
                    // ❌ NO confiar en frontend
                    $precio = $itemDB->precio;
                } else {
                    // ✅ servicio editable
                    $precio = $item['precio'] ?? $itemDB->precio;
                }

                $cantidad = $item['cantidad'];
                $sub = $cantidad * $precio;

                CotizacionDetalle::create([
                    'cotizacion_id' => $cotizacion->id,
                    'item_id' => $itemDB->id,
                    'descripcion' => $itemDB->nombre,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $sub
                ]);

                $subtotal += $sub;
            }

            // 🔹 CALCULAR TOTALES
            $igv = round($subtotal * 0.18, 2);
            $total = round($subtotal + $igv, 2);

            $cotizacion->update([
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cotización creada correctamente',
                'data' => $cotizacion->load('detalles.item')
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /* 🔥 MOSTRAR */
    public function show($id)
    {
        $cotizacion = Cotizacion::with('cliente', 'detalles.item')->findOrFail($id);

        return response()->json($cotizacion);
    }

    /* 🔥 ELIMINAR */
    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        $cotizacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cotización eliminada correctamente'
        ]);
    }
}