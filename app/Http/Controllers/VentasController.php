<?php

namespace App\Http\Controllers;

use App\Jobs\ProcesarFacturaJob;
use App\Models\NotasCreditoModel\Nota;
use App\Models\VentasModel\Venta;
use Illuminate\Http\Request;

class VentasController extends Controller
{
    public function listaFacturas(Request $request)
    {
        $query = Venta::query()->with('emisor:id,name,email');

        // 🔍 BUSCADOR
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('numero_comprobante', 'like', "%$search%")
                  ->orWhere('nombre_cliente', 'like', "%$search%")
                  ->orWhere('numero_documento_cliente', 'like', "%$search%");
            });
        }

        // 📌 FILTRO ESTADO
        if ($request->filled('estado')) {
            $query->where('estado_envio', $request->estado);
        }

        // 📅 FILTRO FECHA
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }

        // 🔽 CONSULTA FINAL
        $facturas = $query->select(
                'id',
                'numero_comprobante',
                'tipo_documento',
                'serie',
                'correlativo',
                'nombre_cliente',
                'numero_documento_cliente',
                'emisor_user_id',
                'fecha_emision',
                'moneda',
                'total_venta',
                'estado_envio',
                'sunat_enviado',
                'mensaje_error',
                'codigo_respuesta_sunat',
                'descripcion_respuesta_sunat',
                'archivo_pdf',
                'archivo_xml',
            )
            ->withCount([
                'notasCredito as notas_credito_count' => function ($q) {
                    $q->where('tipo_documento', '07');
                },
            ])
            ->addSelect([
                'ultima_nota_credito' => Nota::query()
                    ->select('numero_comprobante')
                    ->whereColumn('venta_id', 'ventas.id')
                    ->where('tipo_documento', '07')
                    ->orderByDesc('id')
                    ->limit(1),
            ])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json($facturas);
    }

    public function reporteVentas(Request $request)
    {
        $query = Venta::query();
        $this->applyFacturasFilters($query, $request);

        $resumen = (clone $query)
            ->selectRaw('
                COUNT(*) AS total_comprobantes,
                SUM(CASE WHEN estado_envio = "aceptado" THEN 1 ELSE 0 END) AS aceptados,
                SUM(CASE WHEN estado_envio = "rechazado" THEN 1 ELSE 0 END) AS rechazados,
                SUM(CASE WHEN estado_envio = "pendiente" THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN estado_envio = "error" THEN 1 ELSE 0 END) AS errores,
                COALESCE(SUM(CASE WHEN moneda = "PEN" THEN total_venta ELSE 0 END), 0) AS total_pen,
                COALESCE(SUM(CASE WHEN moneda = "USD" THEN total_venta ELSE 0 END), 0) AS total_usd,
                COALESCE(SUM(total_venta), 0) AS total_general
            ')
            ->first();

        $porEstado = (clone $query)
            ->selectRaw('estado_envio, COUNT(*) AS cantidad, COALESCE(SUM(total_venta), 0) AS total')
            ->groupBy('estado_envio')
            ->orderByDesc('cantidad')
            ->get();

        $porTipoDocumento = (clone $query)
            ->selectRaw('tipo_documento, COUNT(*) AS cantidad, COALESCE(SUM(total_venta), 0) AS total')
            ->groupBy('tipo_documento')
            ->orderByDesc('cantidad')
            ->get();

        $perPage = max((int) $request->get('per_page', 12), 1);
        $ventas = (clone $query)
            ->with('emisor:id,name,email')
            ->select(
                'id',
                'numero_comprobante',
                'tipo_documento',
                'fecha_emision',
                'nombre_cliente',
                'numero_documento_cliente',
                'moneda',
                'total_venta',
                'estado_envio',
                'emisor_user_id'
            )
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'resumen' => [
                'total_comprobantes' => (int) ($resumen->total_comprobantes ?? 0),
                'aceptados' => (int) ($resumen->aceptados ?? 0),
                'rechazados' => (int) ($resumen->rechazados ?? 0),
                'pendientes' => (int) ($resumen->pendientes ?? 0),
                'errores' => (int) ($resumen->errores ?? 0),
                'total_pen' => (float) ($resumen->total_pen ?? 0),
                'total_usd' => (float) ($resumen->total_usd ?? 0),
                'total_general' => (float) ($resumen->total_general ?? 0),
            ],
            'por_estado' => $porEstado,
            'por_tipo_documento' => $porTipoDocumento,
            'ventas' => $ventas,
        ]);
    }

    public function reintentarFactura($id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada',
            ], 404);
        }

        if ($venta->sunat_enviado) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede reintentar una factura ya enviada a SUNAT.',
            ], 422);
        }

        if (!in_array($venta->estado_envio, ['error', 'pendiente'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede reintentar cuando el estado es error o pendiente.',
            ], 422);
        }

        $venta->update([
            'estado_envio' => 'pendiente',
            'mensaje_error' => null,
        ]);

        ProcesarFacturaJob::dispatch($venta->id);

        return response()->json([
            'success' => true,
            'message' => 'Factura enviada nuevamente a proceso.',
        ]);
    }

    public function duplicarRechazada($id)
    {
        $venta = Venta::with('detalles')->find($id);

        if (!$venta) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada',
            ], 404);
        }

        if ($venta->estado_envio !== 'rechazado') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se permite duplicar facturas rechazadas.',
            ], 422);
        }

        $payload = [
            'tipo_documento' => $venta->tipo_documento,
            'fecha_emision' => now()->format('Y-m-d H:i:s'),
            'moneda' => $venta->moneda,
            'forma_pago' => $venta->forma_pago ?: 'contado',
            'observacion' => $venta->observacion,
            'cliente' => [
                'tipo_doc' => (string) $venta->tipo_documento_cliente,
                'num_doc' => (string) $venta->numero_documento_cliente,
                'razon_social' => (string) $venta->nombre_cliente,
            ],
            'items' => $venta->detalles->map(function ($detalle) {
                return [
                    'tipo_item' => $detalle->tipo_item ?? 'producto',
                    'item_id' => $detalle->item_id,
                    'codigo' => $detalle->codigo_producto,
                    'descripcion' => $detalle->descripcion,
                    'unidad' => $detalle->unidad,
                    'cantidad' => (float) $detalle->cantidad,
                    'valor_unitario' => (float) $detalle->valor_unitario,
                    'descuento' => (float) $detalle->descuento,
                    'tip_afe_igv' => (string) ($detalle->tip_afe_igv ?? '10'),
                ];
            })->values()->toArray(),
            'credito' => $venta->forma_pago === 'credito'
                ? [
                    'cuotas' => (int) ($venta->credito_total_cuotas ?? 1),
                    'fecha_vencimiento' => optional($venta->credito_fecha_vencimiento)->format('Y-m-d'),
                    'monto_pendiente' => (float) ($venta->credito_monto_pendiente ?? 0),
                ]
                : null,
            'detraccion' => [
                'aplica' => (bool) ($venta->detraccion_aplica ?? false),
                'codigo' => $venta->detraccion_codigo,
                'porcentaje' => (float) ($venta->detraccion_porcentaje ?? 0),
                'monto' => (float) ($venta->detraccion_monto ?? 0),
                'cuenta' => $venta->detraccion_cuenta,
                'medio_pago' => $venta->detraccion_medio_pago,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Se cargaron los datos para corregir la factura rechazada.',
            'numero_original' => $venta->numero_comprobante,
            'motivo_rechazo' => $venta->descripcion_respuesta_sunat ?: $venta->mensaje_error,
            'payload' => $payload,
        ]);
    }

    private function applyFacturasFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('numero_comprobante', 'like', "%{$search}%")
                    ->orWhere('nombre_cliente', 'like', "%{$search}%")
                    ->orWhere('numero_documento_cliente', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado_envio', (string) $request->estado);
        }

        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', (string) $request->tipo_documento);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }
    }
}
