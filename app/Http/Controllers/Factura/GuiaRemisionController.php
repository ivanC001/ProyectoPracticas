<?php

namespace App\Http\Controllers\Factura;

use App\Jobs\ProcesarGuiaRemisionJob;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuiaRemisionRequest;
use App\Models\GuiasModel\GuiaRemision;
use App\Models\VentasModel\SerieCorrelativo;
use App\Models\VentasModel\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuiaRemisionController extends Controller
{
    public function index(Request $request)
    {
        $query = GuiaRemision::query()->with([
            'detalles',
            'venta:id,numero_comprobante,nombre_cliente,numero_documento_cliente,tipo_documento_cliente,fecha_emision,moneda,total_venta',
        ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('numero_guia', 'like', '%' . $search . '%')
                    ->orWhere('destinatario_razon_social', 'like', '%' . $search . '%')
                    ->orWhere('destinatario_num_doc', 'like', '%' . $search . '%')
                    ->orWhere('transportista_razon_social', 'like', '%' . $search . '%')
                    ->orWhere('vehiculo_placa', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', (string) $request->tipo_documento);
        }

        $guias = $query->orderByDesc('id')
            ->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Listado de guias de remision',
            'data' => $guias->items(),
            'pagination' => [
                'total' => $guias->total(),
                'per_page' => $guias->perPage(),
                'current_page' => $guias->currentPage(),
                'last_page' => $guias->lastPage(),
            ],
        ]);
    }

    public function store(StoreGuiaRemisionRequest $request)
    {
        $payload = $request->validated();
        $detalles = $this->resolveDetallesPayload($payload);

        $guia = DB::transaction(function () use ($payload, $detalles) {
            $correlativo = SerieCorrelativo::obtenerSiguienteCorrelativo($payload['tipo_documento']);

            $guia = GuiaRemision::create([
                'tipo_documento' => $payload['tipo_documento'],
                'serie' => $correlativo['serie'],
                'correlativo' => $correlativo['correlativo'],
                'numero_guia' => $correlativo['numero_comprobante'],
                'fecha_emision' => $payload['fecha_emision'],
                'fecha_traslado' => $payload['fecha_traslado'],
                'motivo_traslado_codigo' => $payload['motivo_traslado_codigo'],
                'motivo_traslado_descripcion' => $payload['motivo_traslado_descripcion'],
                'modalidad_transporte' => $payload['modalidad_transporte'],
                'peso_total' => (float) $payload['peso_total'],
                'unidad_peso' => $payload['unidad_peso'] ?? 'KGM',
                'numero_bultos' => $payload['numero_bultos'] ?? null,
                'observacion' => $payload['observacion'] ?? null,
                'destinatario_tipo_doc' => data_get($payload, 'destinatario.tipo_doc'),
                'destinatario_num_doc' => data_get($payload, 'destinatario.num_doc'),
                'destinatario_razon_social' => data_get($payload, 'destinatario.razon_social'),
                'partida_ubigeo' => data_get($payload, 'partida.ubigeo'),
                'partida_direccion' => data_get($payload, 'partida.direccion'),
                'llegada_ubigeo' => data_get($payload, 'llegada.ubigeo'),
                'llegada_direccion' => data_get($payload, 'llegada.direccion'),
                'transportista_tipo_doc' => data_get($payload, 'transportista.tipo_doc'),
                'transportista_num_doc' => data_get($payload, 'transportista.num_doc'),
                'transportista_razon_social' => data_get($payload, 'transportista.razon_social'),
                'transportista_reg_mtc' => data_get($payload, 'transportista.reg_mtc'),
                'conductor_tipo_doc' => data_get($payload, 'conductor.tipo_doc'),
                'conductor_num_doc' => data_get($payload, 'conductor.num_doc'),
                'conductor_nombres' => data_get($payload, 'conductor.nombres'),
                'conductor_licencia' => data_get($payload, 'conductor.licencia'),
                'vehiculo_placa' => data_get($payload, 'vehiculo.placa'),
                'vehiculo_secundario_placa' => data_get($payload, 'vehiculo.secundario_placa'),
                'venta_id' => $payload['venta_id'] ?? null,
                'estado_envio' => 'pendiente',
            ]);

            foreach ($detalles as $detalle) {
                $guia->detalles()->create([
                    'tipo_item' => $detalle['tipo_item'] ?? null,
                    'item_id' => $detalle['item_id'] ?? null,
                    'codigo' => $detalle['codigo'] ?? null,
                    'descripcion' => $detalle['descripcion'],
                    'unidad' => $detalle['unidad'] ?? 'NIU',
                    'cantidad' => (float) $detalle['cantidad'],
                ]);
            }

            return $guia->fresh('detalles');
        });

        ProcesarGuiaRemisionJob::dispatch((int) $guia->id);

        return response()->json([
            'success' => true,
            'message' => 'Guia registrada y enviada a proceso SUNAT',
            'data' => $guia,
        ], 201);
    }

    public function show(int $id)
    {
        $guia = GuiaRemision::with([
            'detalles',
            'venta:id,numero_comprobante,nombre_cliente,numero_documento_cliente,tipo_documento_cliente,fecha_emision,moneda,total_venta',
            'venta.detalles:id,venta_id,tipo_item,item_id,codigo_producto,descripcion,unidad,cantidad',
        ])->find($id);

        if (!$guia) {
            throw ValidationException::withMessages([
                'guia' => ['Guia de remision no encontrada.'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guia de remision encontrada',
            'data' => $guia,
        ]);
    }

    public function facturasRelacionadas(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = Venta::query()
            ->where('estado_envio', 'aceptado')
            ->whereIn('tipo_documento', ['01', '03'])
            ->orderByDesc('id')
            ->select([
                'id',
                'numero_comprobante',
                'tipo_documento',
                'fecha_emision',
                'nombre_cliente',
                'tipo_documento_cliente',
                'numero_documento_cliente',
                'total_venta',
                'moneda',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('numero_comprobante', 'like', "%{$search}%")
                    ->orWhere('nombre_cliente', 'like', "%{$search}%")
                    ->orWhere('numero_documento_cliente', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(20)->get(),
        ]);
    }

    public function facturaRelacionada(int $id)
    {
        $venta = Venta::query()
            ->with([
                'detalles:id,venta_id,tipo_item,item_id,codigo_producto,descripcion,unidad,cantidad',
            ])
            ->select([
                'id',
                'numero_comprobante',
                'tipo_documento',
                'fecha_emision',
                'nombre_cliente',
                'tipo_documento_cliente',
                'numero_documento_cliente',
                'total_venta',
                'moneda',
                'estado_envio',
            ])
            ->find($id);

        if (!$venta) {
            throw ValidationException::withMessages([
                'venta_id' => ['La factura relacionada no existe.'],
            ]);
        }

        if ($venta->estado_envio !== 'aceptado') {
            throw ValidationException::withMessages([
                'venta_id' => ['Solo puedes relacionar facturas aceptadas por SUNAT.'],
            ]);
        }

        if (!in_array((string) $venta->tipo_documento, ['01', '03'], true)) {
            throw ValidationException::withMessages([
                'venta_id' => ['El comprobante seleccionado no es factura/boleta valida para guia.'],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $venta,
        ]);
    }

    public function update(StoreGuiaRemisionRequest $request, int $id)
    {
        $guia = GuiaRemision::with('detalles')->find($id);

        if (!$guia) {
            throw ValidationException::withMessages([
                'guia' => ['Guia de remision no encontrada.'],
            ]);
        }

        if ($guia->estado_envio === 'aceptado') {
            throw ValidationException::withMessages([
                'guia' => ['No puedes editar una guia ya aceptada por SUNAT.'],
            ]);
        }

        $payload = $request->validated();
        $detalles = $this->resolveDetallesPayload($payload);

        DB::transaction(function () use ($guia, $payload, $detalles) {
            $guia->update([
                'tipo_documento' => $payload['tipo_documento'],
                'fecha_emision' => $payload['fecha_emision'],
                'fecha_traslado' => $payload['fecha_traslado'],
                'motivo_traslado_codigo' => $payload['motivo_traslado_codigo'],
                'motivo_traslado_descripcion' => $payload['motivo_traslado_descripcion'],
                'modalidad_transporte' => $payload['modalidad_transporte'],
                'peso_total' => (float) $payload['peso_total'],
                'unidad_peso' => $payload['unidad_peso'] ?? 'KGM',
                'numero_bultos' => $payload['numero_bultos'] ?? null,
                'observacion' => $payload['observacion'] ?? null,
                'destinatario_tipo_doc' => data_get($payload, 'destinatario.tipo_doc'),
                'destinatario_num_doc' => data_get($payload, 'destinatario.num_doc'),
                'destinatario_razon_social' => data_get($payload, 'destinatario.razon_social'),
                'partida_ubigeo' => data_get($payload, 'partida.ubigeo'),
                'partida_direccion' => data_get($payload, 'partida.direccion'),
                'llegada_ubigeo' => data_get($payload, 'llegada.ubigeo'),
                'llegada_direccion' => data_get($payload, 'llegada.direccion'),
                'transportista_tipo_doc' => data_get($payload, 'transportista.tipo_doc'),
                'transportista_num_doc' => data_get($payload, 'transportista.num_doc'),
                'transportista_razon_social' => data_get($payload, 'transportista.razon_social'),
                'transportista_reg_mtc' => data_get($payload, 'transportista.reg_mtc'),
                'conductor_tipo_doc' => data_get($payload, 'conductor.tipo_doc'),
                'conductor_num_doc' => data_get($payload, 'conductor.num_doc'),
                'conductor_nombres' => data_get($payload, 'conductor.nombres'),
                'conductor_licencia' => data_get($payload, 'conductor.licencia'),
                'vehiculo_placa' => data_get($payload, 'vehiculo.placa'),
                'vehiculo_secundario_placa' => data_get($payload, 'vehiculo.secundario_placa'),
                'venta_id' => $payload['venta_id'] ?? null,
                'estado_envio' => 'pendiente',
                'sunat_enviado' => false,
                'fecha_envio_sunat' => null,
                'codigo_respuesta_sunat' => null,
                'descripcion_respuesta_sunat' => null,
                'mensaje_error' => null,
                'hash_cpe' => null,
                'archivo_xml' => null,
                'archivo_pdf' => null,
                'archivo_cdr' => null,
            ]);

            $guia->detalles()->delete();

            foreach ($detalles as $detalle) {
                $guia->detalles()->create([
                    'tipo_item' => $detalle['tipo_item'] ?? null,
                    'item_id' => $detalle['item_id'] ?? null,
                    'codigo' => $detalle['codigo'] ?? null,
                    'descripcion' => $detalle['descripcion'],
                    'unidad' => $detalle['unidad'] ?? 'NIU',
                    'cantidad' => (float) $detalle['cantidad'],
                ]);
            }
        });

        ProcesarGuiaRemisionJob::dispatch((int) $guia->id);

        return response()->json([
            'success' => true,
            'message' => 'Guia actualizada y reenviada a proceso SUNAT',
            'data' => $guia->fresh('detalles'),
        ]);
    }

    public function destroy(int $id)
    {
        $guia = GuiaRemision::find($id);

        if (!$guia) {
            throw ValidationException::withMessages([
                'guia' => ['Guia de remision no encontrada.'],
            ]);
        }

        $guia->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guia de remision eliminada correctamente',
        ]);
    }

    protected function resolveDetallesPayload(array $payload): array
    {
        $detalles = collect((array) ($payload['detalles'] ?? []))
            ->filter(function ($item) {
                return trim((string) data_get($item, 'descripcion', '')) !== '';
            })
            ->values();

        if ($detalles->isNotEmpty()) {
            return $detalles->all();
        }

        $ventaId = (int) ($payload['venta_id'] ?? 0);
        if ($ventaId <= 0) {
            return [];
        }

        $venta = Venta::query()->with('detalles')->find($ventaId);
        if (!$venta) {
            return [];
        }

        return $venta->detalles->map(function ($detalle) {
            return [
                'tipo_item' => $detalle->tipo_item ?? null,
                'item_id' => $detalle->item_id ?? null,
                'codigo' => $detalle->codigo_producto ?? null,
                'descripcion' => $detalle->descripcion,
                'unidad' => $detalle->unidad ?: 'NIU',
                'cantidad' => (float) $detalle->cantidad,
            ];
        })->values()->all();
    }
}
