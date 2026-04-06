<?php

namespace App\Http\Controllers\ControladorCotizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\CotizacionRequest;
use App\Models\CotizacionModel\Cotizacion;
use App\Models\CotizacionModel\CotizacionDetalle;
use App\Models\ProductosModel\Producto;
use App\Models\ProductosModel\Servicio;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Luecano\NumeroALetras\NumeroALetras;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Cotizacion::select(
                'id',
                'cliente_id',
                'fecha',
                'total',
                'estado',
                'asunto'
            )
            ->with(['cliente:id,razon_social'])
            ->withCount('detalles');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('asunto', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                        $clienteQuery->where('razon_social', 'like', "%{$search}%");
                    })
                    ->orWhereHas('detalles', function ($detalleQuery) use ($search) {
                        $detalleQuery->where('nombre_item', 'like', "%{$search}%");
                    });
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
            ],
        ]);
    }

    public function store(CotizacionRequest $request)
    {
        try {
            $data = $request->validated();

            $cotizacion = DB::transaction(function () use ($data) {
                $cotizacion = Cotizacion::create($this->buildCotizacionPayload($data));
                $this->syncDetalles($cotizacion, $data['items']);

                return $cotizacion->fresh();
            });

            return response()->json([
                'success' => true,
                'message' => "Cotizacion #{$cotizacion->id} creada",
                'data' => [
                    'id' => $cotizacion->id,
                    'asunto' => $cotizacion->asunto,
                ],
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(CotizacionRequest $request, $id)
    {
        $cotizacion = Cotizacion::find($id);

        if (!$cotizacion) {
            throw ValidationException::withMessages([
                'cotizacion' => ['No encontrada'],
            ]);
        }

        try {
            $data = $request->validated();

            DB::transaction(function () use ($cotizacion, $data) {
                $cotizacion->update($this->buildCotizacionPayload($data, $cotizacion));
                $this->syncDetalles($cotizacion, $data['items']);
            });

            $cotizacion->refresh();

            return response()->json([
                'success' => true,
                'message' => "Cotizacion #{$cotizacion->id} actualizada",
                'data' => [
                    'id' => $cotizacion->id,
                    'asunto' => $cotizacion->asunto,
                ],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $cotizacion = Cotizacion::with([
            'cliente:id,razon_social,num_doc',
            'detalles',
        ])->find($id);

        if (!$cotizacion) {
            throw ValidationException::withMessages([
                'cotizacion' => ['No encontrada'],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $cotizacion,
        ]);
    }

    public function destroy($id)
    {
        $cotizacion = Cotizacion::find($id);

        if (!$cotizacion) {
            throw ValidationException::withMessages([
                'cotizacion' => ['No encontrada'],
            ]);
        }

        $cotizacion->delete();

        return response()->json([
            'success' => true,
            'message' => "Cotizacion #{$id} eliminada",
        ]);
    }

    public function pdf($id)
    {
        $cotizacion = Cotizacion::with([
            'cliente',
            'detalles',
        ])->find($id);

        if (!$cotizacion) {
            abort(404, 'Cotizacion no encontrada');
        }

        $formatter = new NumeroALetras();
        $fecha = $cotizacion->fecha
            ? $cotizacion->fecha->locale('es')->translatedFormat('d \\d\\e F \\d\\e\\l Y')
            : now()->locale('es')->translatedFormat('d \\d\\e F \\d\\e\\l Y');

        $numeroCotizacion = str_pad((string) $cotizacion->id, 4, '0', STR_PAD_LEFT)
            . '-'
            . ($cotizacion->fecha?->format('Y') ?? now()->format('Y'));

        $tipoCotizacion = $this->resolveTipoCotizacion($cotizacion);
        $mediosPagoConfigurados = collect(config('empresa.medios_pago', []));
        $mediosPagoGuardados = $cotizacion->medios_pago;
        $mediosPagoSeleccionados = collect(is_array($mediosPagoGuardados) ? $mediosPagoGuardados : $mediosPagoConfigurados->keys()->all())
            ->filter(fn ($key) => $mediosPagoConfigurados->has($key))
            ->values();
        $mediosPago = $mediosPagoSeleccionados
            ->map(fn ($key) => ['key' => $key] + $mediosPagoConfigurados->get($key))
            ->values();

        $notas = collect(preg_split('/\r\n|\r|\n/', (string) $cotizacion->notas))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        $html = view('vistaCotizacion.pdf', [
            'cotizacion' => $cotizacion,
            'tipoCotizacion' => $tipoCotizacion,
            'fechaTexto' => mb_strtoupper($fecha, 'UTF-8'),
            'numeroCotizacion' => $numeroCotizacion,
            'totalLetras' => $formatter->toInvoice((float) $cotizacion->total, 2, 'SOLES'),
            'tipoDocumentoCliente' => $this->resolveTipoDocumentoCliente($cotizacion->cliente?->tipo_doc),
            'notas' => $notas,
            'mediosPago' => $mediosPago,
            'introTexto' => $this->resolveIntroTexto($tipoCotizacion),
            'logoBase64' => $this->resolveLogoBase64(),
            'empresa' => [
                'razon_social' => config('empresa.razon_social'),
                'ruc' => config('empresa.ruc'),
                'direccion' => config('empresa.direccion'),
                'distrito' => config('empresa.distrito'),
                'provincia' => config('empresa.provincia'),
                'departamento' => config('empresa.departamento'),
                'telefono' => config('empresa.telefono'),
                'emails' => config('empresa.emails', []),
                'gerente_cargo' => config('empresa.gerente_cargo'),
                'gerente_nombre' => config('empresa.gerente_nombre'),
            ],
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="cotizacion-' . $cotizacion->id . '.pdf"',
        ]);
    }

    private function buildCotizacionPayload(array $data, ?Cotizacion $cotizacion = null): array
    {
        return [
            'cliente_id' => $data['cliente_id'],
            'asunto' => $data['asunto'] ?? $cotizacion?->asunto ?? 'Cotizacion',
            'fecha' => $data['fecha'] ?? ($cotizacion?->fecha?->toDateString() ?? now()->toDateString()),
            'descripcion_general' => $data['descripcion_general'] ?? null,
            'notas' => $data['notas'] ?? null,
            'medios_pago' => $data['medios_pago'] ?? $cotizacion?->medios_pago ?? array_keys(config('empresa.medios_pago', [])),
            'estado' => $data['estado'] ?? $cotizacion?->estado ?? 'borrador',
            'subtotal' => $cotizacion?->subtotal ?? 0,
            'igv' => $cotizacion?->igv ?? 0,
            'total' => $cotizacion?->total ?? 0,
        ];
    }

    private function syncDetalles(Cotizacion $cotizacion, array $items): void
    {
        $cotizacion->detalles()->delete();

        $subtotal = 0;

        foreach ($items as $index => $item) {
            $detalle = $this->resolveDetalleData($item, $index);
            $sub = round($item['cantidad'] * $detalle['precio'], 2);

            CotizacionDetalle::create([
                'cotizacion_id' => $cotizacion->id,
                'tipo' => $item['tipo'],
                'producto_id' => $detalle['producto_id'],
                'servicio_id' => $detalle['servicio_id'],
                'codigo_item' => $detalle['codigo_item'],
                'nombre_item' => $detalle['nombre_item'],
                'unidad' => $detalle['unidad'],
                'detalle_servicio' => $detalle['detalle_servicio'],
                'cantidad' => $item['cantidad'],
                'precio' => $detalle['precio'],
                'subtotal' => $sub,
            ]);

            $subtotal += $sub;
        }

        $igv = round($subtotal * 0.18, 2);
        $total = round($subtotal + $igv, 2);

        $cotizacion->update([
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $total,
        ]);
    }

    private function resolveDetalleData(array $item, int $index): array
    {
        $detalleManual = collect($item['detalle_servicio'] ?? [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        if ($item['tipo'] === 'producto') {
            $producto = Producto::where('activo', true)->find($item['producto_id']);

            if (!$producto) {
                throw ValidationException::withMessages([
                    "items.$index.producto_id" => ['El producto seleccionado no existe o esta inactivo.'],
                ]);
            }

            return [
                'producto_id' => $producto->id,
                'servicio_id' => null,
                'codigo_item' => $producto->codigo,
                'nombre_item' => $producto->descripcion,
                'unidad' => $producto->unidad ?? null,
                'detalle_servicio' => $detalleManual ?: null,
                'precio' => $producto->precio,
            ];
        }

        $servicio = Servicio::with('pasos')->where('activo', true)->find($item['servicio_id']);

        if (!$servicio) {
            throw ValidationException::withMessages([
                "items.$index.servicio_id" => ['El servicio seleccionado no existe o esta inactivo.'],
            ]);
        }

        return [
            'producto_id' => null,
            'servicio_id' => $servicio->id,
            'codigo_item' => null,
            'nombre_item' => $servicio->nombre,
            'unidad' => 'servicio',
            'detalle_servicio' => $detalleManual ?: $servicio->pasos->pluck('descripcion')->filter()->values()->all(),
            'precio' => $servicio->precio,
        ];
    }

    private function resolveTipoDocumentoCliente(?string $tipoDoc): string
    {
        return match ((string) $tipoDoc) {
            '1' => 'DNI',
            '6' => 'RUC',
            default => 'DOC',
        };
    }

    private function resolveTipoCotizacion(Cotizacion $cotizacion): string
    {
        $hasProductos = $cotizacion->detalles->contains(fn ($detalle) => $detalle->tipo === 'producto');
        $hasServicios = $cotizacion->detalles->contains(fn ($detalle) => $detalle->tipo === 'servicio');

        if ($hasProductos && $hasServicios) {
            return 'mixta';
        }

        if ($hasServicios) {
            return 'servicios';
        }

        return 'productos';
    }

    private function resolveIntroTexto(string $tipoCotizacion): string
    {
        return match ($tipoCotizacion) {
            'servicios' => 'Adjunto el presupuesto por los servicios a realizarse segun lo solicitado por ustedes, el cual detallamos a continuacion:',
            'productos' => 'Adjunto la cotizacion por los productos solicitados por ustedes, la cual detallamos a continuacion:',
            default => 'Adjunto el presupuesto por los trabajos y suministros solicitados, el cual detallamos a continuacion:',
        };
    }

    private function resolveLogoBase64(): ?string
    {
        $logoPath = public_path('assets/dist/img/AdminLTELogo.png');

        if (!is_file($logoPath)) {
            return null;
        }

        $mime = mime_content_type($logoPath) ?: 'image/png';
        $contents = base64_encode(file_get_contents($logoPath));

        return "data:{$mime};base64,{$contents}";
    }
}
