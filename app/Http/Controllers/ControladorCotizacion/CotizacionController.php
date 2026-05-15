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
                'moneda',
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

        $incluyeIgvPorNotas = $this->resolveIncluyeIgvFromNotas($cotizacion->notas);
        $incluyeIgv = $incluyeIgvPorNotas ?? (bool) ($cotizacion->incluye_igv ?? ((float) $cotizacion->igv > 0));
        $montoIgv = $incluyeIgv ? (float) $cotizacion->igv : 0;
        $montoTotal = $incluyeIgv ? (float) $cotizacion->total : (float) $cotizacion->subtotal;
        $monedaCotizacion = $this->normalizeMoneda($cotizacion->moneda ?? $cotizacion->detalles->first()?->moneda_precio ?? 'PEN');
        $simboloMoneda = $this->simboloMoneda($monedaCotizacion);
        $monedaTexto = $this->monedaTexto($monedaCotizacion);

        $html = view('vistaCotizacion.pdf', [
            'cotizacion' => $cotizacion,
            'tipoCotizacion' => $tipoCotizacion,
            'fechaTexto' => mb_strtoupper($fecha, 'UTF-8'),
            'numeroCotizacion' => $numeroCotizacion,
            'incluyeIgv' => $incluyeIgv,
            'montoIgv' => $montoIgv,
            'montoTotal' => $montoTotal,
            'totalLetras' => $formatter->toInvoice($montoTotal, 2, $monedaTexto),
            'monedaCotizacion' => $monedaCotizacion,
            'simboloMoneda' => $simboloMoneda,
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
        $incluyeIgv = $this->resolveIncluyeIgv($data, $cotizacion);

        return [
            'cliente_id' => $data['cliente_id'],
            'asunto' => $data['asunto'] ?? $cotizacion?->asunto ?? 'Cotizacion',
            'fecha' => $data['fecha'] ?? ($cotizacion?->fecha?->toDateString() ?? now()->toDateString()),
            'moneda' => $this->normalizeMoneda($data['moneda'] ?? $cotizacion?->moneda ?? 'PEN'),
            'tipo_cambio' => $this->normalizeTipoCambio($data['tipo_cambio'] ?? $cotizacion?->tipo_cambio ?? 3.8),
            'descripcion_general' => $data['descripcion_general'] ?? null,
            'notas' => $data['notas'] ?? null,
            'medios_pago' => $data['medios_pago'] ?? $cotizacion?->medios_pago ?? array_keys(config('empresa.medios_pago', [])),
            'incluye_igv' => $incluyeIgv,
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
        $monedaCotizacion = $this->normalizeMoneda($cotizacion->moneda ?? 'PEN');
        $tipoCambio = $this->normalizeTipoCambio($cotizacion->tipo_cambio ?? 3.8);

        foreach ($items as $index => $item) {
            $detalle = $this->resolveDetalleData($item, $index);
            $sub = round($item['cantidad'] * $detalle['precio'], 2);
            $monedaDetalle = $this->normalizeMoneda($detalle['moneda_precio'] ?? 'PEN');
            $subConvertido = $this->convertAmount(
                $sub,
                $monedaDetalle,
                $monedaCotizacion,
                $tipoCambio,
                'tipo_cambio'
            );

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
                'moneda_precio' => $monedaDetalle,
                'subtotal' => $sub,
            ]);

            $subtotal += $subConvertido;
        }

        $subtotal = round($subtotal, 2);
        $incluyeIgv = (bool) ($cotizacion->incluye_igv ?? true);
        $igv = $incluyeIgv ? round($subtotal * 0.18, 2) : 0;
        $total = round($subtotal + $igv, 2);

        $cotizacion->update([
            'moneda' => $monedaCotizacion,
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $total,
        ]);
    }

    private function resolveIncluyeIgv(array $data, ?Cotizacion $cotizacion = null): bool
    {
        $incluyeIgvPorNotas = $this->resolveIncluyeIgvFromNotas($data['notas'] ?? null);

        if ($incluyeIgvPorNotas !== null) {
            return $incluyeIgvPorNotas;
        }

        if (array_key_exists('incluye_igv', $data) && $data['incluye_igv'] !== null) {
            return (bool) $data['incluye_igv'];
        }

        if ($cotizacion) {
            $incluyeIgvPorNotasGuardadas = $this->resolveIncluyeIgvFromNotas($cotizacion->notas);

            if ($incluyeIgvPorNotasGuardadas !== null) {
                return $incluyeIgvPorNotasGuardadas;
            }

            if ($cotizacion->incluye_igv !== null) {
                return (bool) $cotizacion->incluye_igv;
            }

            return (float) $cotizacion->igv > 0;
        }

        return true;
    }

    private function resolveIncluyeIgvFromNotas(?string $notas): ?bool
    {
        if (!$notas) {
            return null;
        }

        $lineas = preg_split('/\r\n|\r|\n/', $notas) ?: [];
        $decision = null;

        foreach ($lineas as $linea) {
            $normalizada = $this->normalizeText($linea);

            if (str_contains($normalizada, 'no incluye igv')) {
                $decision = false;
                continue;
            }

            if (str_contains($normalizada, 'incluye igv')) {
                $decision = true;
            }
        }

        return $decision;
    }

    private function normalizeText(string $text): string
    {
        $normalized = mb_strtolower(trim($text), 'UTF-8');

        return strtr($normalized, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ä' => 'a',
            'ë' => 'e',
            'ï' => 'i',
            'ö' => 'o',
            'ü' => 'u',
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
                'precio' => $this->resolvePrecioItem($item, (float) $producto->precio),
                'moneda_precio' => $this->normalizeMoneda(data_get($item, 'moneda_precio', $producto->moneda_precio ?? 'PEN')),
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
            'precio' => $this->resolvePrecioItem($item, (float) $servicio->precio),
            'moneda_precio' => $this->normalizeMoneda(data_get($item, 'moneda_precio', $servicio->moneda_precio ?? 'PEN')),
        ];
    }

    private function normalizeTipoCambio($rate): float
    {
        $value = (float) $rate;
        return $value > 0 ? round($value, 4) : 0;
    }

    private function convertAmount(
        float $amount,
        string $fromCurrency,
        string $toCurrency,
        float $exchangeRate,
        string $errorKey = 'tipo_cambio'
    ): float {
        $from = $this->normalizeMoneda($fromCurrency);
        $to = $this->normalizeMoneda($toCurrency);

        if ($from === $to) {
            return round($amount, 2);
        }

        if ($exchangeRate <= 0) {
            throw ValidationException::withMessages([
                $errorKey => ['Debes ingresar un tipo de cambio mayor a 0 para convertir entre PEN y USD.'],
            ]);
        }

        if ($from === 'USD' && $to === 'PEN') {
            return round($amount * $exchangeRate, 2);
        }

        if ($from === 'PEN' && $to === 'USD') {
            return round($amount / $exchangeRate, 2);
        }

        return round($amount, 2);
    }

    private function resolvePrecioItem(array $item, float $defaultPrice): float
    {
        if (array_key_exists('precio', $item) && $item['precio'] !== null && $item['precio'] !== '') {
            return round(max(0, (float) $item['precio']), 2);
        }

        return round(max(0, $defaultPrice), 2);
    }

    private function normalizeMoneda(?string $moneda): string
    {
        return strtoupper((string) $moneda) === 'USD' ? 'USD' : 'PEN';
    }

    private function simboloMoneda(string $moneda): string
    {
        return $this->normalizeMoneda($moneda) === 'USD' ? 'US$' : 'S/';
    }

    private function monedaTexto(string $moneda): string
    {
        return $this->normalizeMoneda($moneda) === 'USD'
            ? 'DOLARES AMERICANOS'
            : 'SOLES';
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
