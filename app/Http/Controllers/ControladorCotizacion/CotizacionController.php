<?php

namespace App\Http\Controllers\ControladorCotizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\CotizacionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use App\Models\CotizacionModel\Cotizacion;
use App\Models\CotizacionModel\CotizacionDetalle;
use App\Models\ProductosModel\Producto;
use App\Models\ProductosModel\Servicio;


class CotizacionController extends Controller
{

    /**
     * 🔥 LISTAR
     */
    public function index(Request $request)
    {
        $query = Cotizacion::with('cliente', 'detalles');

        if ($request->filled('search')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('razon_social', 'like', "%{$request->search}%");
            });
        }

        $data = $query->orderBy('id', 'desc')
                      ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total()
            ]
        ]);
    }

    /**
     * 🔥 CREAR
     */
    public function store(CotizacionRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = $request->validated();

            $cotizacion = Cotizacion::create([
                'cliente_id' => $data['cliente_id'],
                'fecha' => now(),
                'subtotal' => 0,
                'igv' => 0,
                'total' => 0,
                'estado' => 'borrador'
            ]);

            $subtotal = 0;

            foreach ($data['items'] as $item) {

                $tipo = $item['tipo'];
                $cantidad = $item['cantidad'];

                $producto_id = null;
                $servicio_id = null;
                $codigo = '';
                $nombre = '';
                $precio = 0;
                $unidad = null;

                /* 🟦 PRODUCTO */
                if ($tipo === 'producto') {

                    $producto = Producto::where('activo', true)
                        ->findOrFail($item['producto_id']);

                    $producto_id = $producto->id;
                    $codigo = $producto->codigo;
                    $nombre = $producto->descripcion;
                    $precio = $producto->precio;
                    $unidad = $producto->unidad ?? null;
                }

                /* 🟩 SERVICIO */
                if ($tipo === 'servicio') {

                    $servicio = Servicio::where('activo', true)
                        ->findOrFail($item['servicio_id']);

                    $servicio_id = $servicio->id;
                    $codigo = $servicio->codigo;
                    $nombre = $servicio->nombre;
                    $precio = $servicio->precio;
                    $unidad = 'servicio';
                }

                $sub = round($cantidad * $precio, 2);

                CotizacionDetalle::create([
                    'cotizacion_id' => $cotizacion->id,
                    'tipo' => $tipo,
                    'producto_id' => $producto_id,
                    'servicio_id' => $servicio_id,
                    'codigo_item' => $codigo,
                    'nombre_item' => $nombre,
                    'unidad' => $unidad,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $sub
                ]);

                $subtotal += $sub;
            }

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
                'data' => $cotizacion->load('cliente','detalles')
            ], 201);

        } catch (ValidationException $e) {

            DB::rollBack();
            throw $e;

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 MOSTRAR
     */
    public function show($id)
    {
        $cotizacion = Cotizacion::with('cliente', 'detalles')->find($id);

        if (!$cotizacion) {
            throw ValidationException::withMessages([
                'cotizacion' => ['No encontrada']
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $cotizacion
        ]);
    }

    /**
     * 🔥 ELIMINAR
     */
    public function destroy($id)
    {
        $cotizacion = Cotizacion::find($id);

        if (!$cotizacion) {
            throw ValidationException::withMessages([
                'cotizacion' => ['No encontrada']
            ]);
        }

        $cotizacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Eliminada correctamente'
        ]);
    }
}