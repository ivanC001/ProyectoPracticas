<?php

namespace App\Http\Controllers\Factura;

use App\Jobs\ProcesarGuiaRemisionJob;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuiaRemisionRequest;
use App\Models\ClientesModel\Cliente;
use App\Models\GuiasModel\GuiaRemision;
use App\Models\VentasModel\SerieCorrelativo;
use App\Models\VentasModel\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuiaRemisionController extends Controller
{
    public function index(Request $request)
    {
        $query = GuiaRemision::query()->with([
            'detalles',
            'venta:id,numero_comprobante,nombre_cliente,numero_documento_cliente,tipo_documento_cliente,fecha_emision,moneda,total_venta',
            'guiaRemitente:id,numero_guia,tipo_documento,estado_envio,fecha_traslado,destinatario_razon_social,destinatario_num_doc',
        ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('numero_guia', 'like', '%' . $search . '%')
                    ->orWhere('destinatario_razon_social', 'like', '%' . $search . '%')
                    ->orWhere('destinatario_num_doc', 'like', '%' . $search . '%')
                    ->orWhere('transportista_razon_social', 'like', '%' . $search . '%')
                    ->orWhere('vehiculo_placa', 'like', '%' . $search . '%')
                    ->orWhere('documento_rel_numero', 'like', '%' . $search . '%');
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
        $documentoRelacionado = $this->resolveDocumentoRelacionadoPayload($payload);

        $guia = DB::transaction(function () use ($payload, $detalles, $documentoRelacionado) {
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
                'guia_remitente_id' => $payload['guia_remitente_id'] ?? null,
                'documento_rel_tipo' => $documentoRelacionado['tipo'],
                'documento_rel_numero' => $documentoRelacionado['numero'],
                'documento_rel_emisor' => $documentoRelacionado['emisor'],
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

            return $guia->fresh(['detalles', 'venta', 'guiaRemitente']);
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
            'guiaRemitente:id,numero_guia,tipo_documento,estado_envio,fecha_traslado,destinatario_razon_social,destinatario_num_doc',
            'guiaRemitente.detalles:id,guia_remision_id,tipo_item,item_id,codigo,descripcion,unidad,cantidad',
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
                'venta_id' => ['El comprobante relacionado no existe.'],
            ]);
        }

        if ($venta->estado_envio !== 'aceptado') {
            throw ValidationException::withMessages([
                'venta_id' => ['Solo puedes relacionar comprobantes aceptados por SUNAT.'],
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

    public function remitentesRelacionados(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = GuiaRemision::query()
            ->where('tipo_documento', '09')
            ->orderByDesc('id')
            ->select([
                'id',
                'numero_guia',
                'fecha_emision',
                'fecha_traslado',
                'destinatario_razon_social',
                'destinatario_num_doc',
                'estado_envio',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('numero_guia', 'like', "%{$search}%")
                    ->orWhere('destinatario_razon_social', 'like', "%{$search}%")
                    ->orWhere('destinatario_num_doc', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(20)->get(),
        ]);
    }

    public function remitenteRelacionado(int $id)
    {
        $guia = GuiaRemision::query()
            ->with([
                'detalles:id,guia_remision_id,tipo_item,item_id,codigo,descripcion,unidad,cantidad',
            ])
            ->select([
                'id',
                'tipo_documento',
                'numero_guia',
                'fecha_emision',
                'fecha_traslado',
                'destinatario_razon_social',
                'destinatario_num_doc',
                'estado_envio',
            ])
            ->find($id);

        if (!$guia) {
            throw ValidationException::withMessages([
                'guia_remitente_id' => ['La guia remitente no existe.'],
            ]);
        }

        if ((string) $guia->tipo_documento !== '09') {
            throw ValidationException::withMessages([
                'guia_remitente_id' => ['La guia relacionada debe ser tipo 09 (remitente).'],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $guia,
        ]);
    }

    public function clientesRelacionados(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = Cliente::query()
            ->where('estado', true)
            ->orderByDesc('id')
            ->select([
                'id',
                'tipo_doc',
                'num_doc',
                'razon_social',
                'direccion',
                'email',
                'telefono',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('num_doc', 'like', "%{$search}%")
                    ->orWhere('razon_social', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(20)->get(),
        ]);
    }

    public function clienteRelacionado(int $id)
    {
        $cliente = Cliente::query()
            ->where('estado', true)
            ->find($id);

        if (!$cliente) {
            throw ValidationException::withMessages([
                'cliente' => ['Cliente no encontrado.'],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $cliente,
        ]);
    }

    public function registrarClienteRelacionado(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_doc' => 'required|in:1,6',
            'num_doc' => 'required|numeric|unique:clientes,num_doc',
            'razon_social' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
        ], [
            'tipo_doc.required' => 'El tipo de documento del cliente es obligatorio.',
            'tipo_doc.in' => 'Para registro rapido solo se permite DNI o RUC.',
            'num_doc.required' => 'El numero de documento es obligatorio.',
            'num_doc.numeric' => 'El numero de documento debe ser numerico.',
            'num_doc.unique' => 'El numero de documento ya esta registrado.',
            'razon_social.required' => 'La razon social es obligatoria.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $tipo = (string) $request->input('tipo_doc');
            $num = preg_replace('/\D+/', '', (string) $request->input('num_doc')) ?? '';

            if ($tipo === '1' && strlen($num) !== 8) {
                $validator->errors()->add('num_doc', 'El DNI debe tener 8 digitos.');
            }

            if ($tipo === '6' && strlen($num) !== 11) {
                $validator->errors()->add('num_doc', 'El RUC debe tener 11 digitos.');
            }
        });

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $cliente = Cliente::create([
            'tipo_doc' => $request->input('tipo_doc'),
            'num_doc' => preg_replace('/\D+/', '', (string) $request->input('num_doc')),
            'razon_social' => trim((string) $request->input('razon_social')),
            'direccion' => $request->input('direccion'),
            'email' => $request->input('email'),
            'telefono' => $request->input('telefono'),
            'estado' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cliente registrado correctamente',
            'data' => $cliente,
        ], 201);
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
        $documentoRelacionado = $this->resolveDocumentoRelacionadoPayload($payload);

        DB::transaction(function () use ($guia, $payload, $detalles, $documentoRelacionado) {
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
                'guia_remitente_id' => $payload['guia_remitente_id'] ?? null,
                'documento_rel_tipo' => $documentoRelacionado['tipo'],
                'documento_rel_numero' => $documentoRelacionado['numero'],
                'documento_rel_emisor' => $documentoRelacionado['emisor'],
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
            'data' => $guia->fresh(['detalles', 'venta', 'guiaRemitente']),
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
        $venta = $ventaId > 0
            ? Venta::query()->with('detalles')->find($ventaId)
            : null;

        if ($venta) {
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

        $guiaRemitenteId = (int) ($payload['guia_remitente_id'] ?? 0);
        if ($guiaRemitenteId <= 0) {
            return [];
        }

        $guiaRemitente = GuiaRemision::query()->with('detalles')->find($guiaRemitenteId);
        if (!$guiaRemitente) {
            return [];
        }

        return $guiaRemitente->detalles->map(function ($detalle) {
            return [
                'tipo_item' => $detalle->tipo_item ?? null,
                'item_id' => $detalle->item_id ?? null,
                'codigo' => $detalle->codigo ?? null,
                'descripcion' => $detalle->descripcion,
                'unidad' => $detalle->unidad ?: 'NIU',
                'cantidad' => (float) $detalle->cantidad,
            ];
        })->values()->all();
    }

    protected function resolveDocumentoRelacionadoPayload(array $payload): array
    {
        $tipo = trim((string) data_get($payload, 'documento_relacionado.tipo', ''));
        $numero = trim((string) data_get($payload, 'documento_relacionado.numero', ''));
        $emisor = trim((string) data_get($payload, 'documento_relacionado.emisor', ''));

        $ventaId = (int) data_get($payload, 'venta_id', 0);
        if ($ventaId > 0) {
            $venta = Venta::query()->select(['id', 'tipo_documento', 'numero_comprobante'])->find($ventaId);
            if ($venta) {
                return [
                    'tipo' => (string) $venta->tipo_documento,
                    'numero' => (string) $venta->numero_comprobante,
                    'emisor' => $emisor !== '' ? $emisor : (string) config('empresa.ruc', ''),
                ];
            }
        }

        $guiaRemitenteId = (int) data_get($payload, 'guia_remitente_id', 0);
        if ($guiaRemitenteId > 0) {
            $guiaRemitente = GuiaRemision::query()
                ->select(['id', 'tipo_documento', 'numero_guia'])
                ->find($guiaRemitenteId);

            if ($guiaRemitente && (string) $guiaRemitente->tipo_documento === '09') {
                return [
                    'tipo' => '09',
                    'numero' => (string) $guiaRemitente->numero_guia,
                    'emisor' => $emisor !== '' ? $emisor : (string) config('empresa.ruc', ''),
                ];
            }
        }

        return [
            'tipo' => $tipo !== '' ? $tipo : null,
            'numero' => $numero !== '' ? $numero : null,
            'emisor' => $emisor !== ''
                ? $emisor
                : ($tipo !== '' && $numero !== '' ? (string) config('empresa.ruc', '') : null),
        ];
    }
}
