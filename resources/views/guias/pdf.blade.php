<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .top-line { border-top: 3px solid #0f3d7a; margin-bottom: 10px; }
        .row { width: 100%; clear: both; }
        .col-left { float: left; width: 66%; }
        .col-right { float: right; width: 32%; text-align: center; border: 2px solid #0f3d7a; border-radius: 8px; overflow: hidden; }
        .title-box { background: #0f3d7a; color: #fff; padding: 8px 6px; font-size: 14px; font-weight: bold; }
        .number-box { padding: 14px 6px; font-size: 28px; font-weight: 700; letter-spacing: 0.8px; }
        .company h1 { font-size: 24px; margin: 4px 0 8px; color: #0f3d7a; }
        .company p { margin: 4px 0; }
        .section { border: 1px solid #c9d4e4; border-radius: 8px; padding: 10px 12px; margin-top: 10px; }
        .section h3 { margin: 0 0 8px; font-size: 13px; color: #0f3d7a; text-transform: uppercase; }
        .kv { margin: 2px 0; }
        .kv strong { display: inline-block; width: 150px; color: #334155; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #0f3d7a; color: #fff; padding: 7px 6px; border: 1px solid #d2dbe8; }
        td { padding: 7px 6px; border: 1px solid #d2dbe8; vertical-align: top; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .muted { color: #6b7280; }
        .footer { margin-top: 14px; border-top: 1px solid #d2dbe8; padding-top: 8px; font-size: 11px; color: #475569; }
        .clearfix { clear: both; }
    </style>
</head>
<body>
    <div class="top-line"></div>
    <div class="row">
        <div class="col-left company">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" style="height: 64px; margin-bottom: 6px;" alt="Logo">
            @endif
            <h1>{{ $empresa['razon_social'] ?? 'EMPRESA' }}</h1>
            <p><strong>RUC:</strong> {{ $empresa['ruc'] ?? '' }}</p>
            <p>{{ $empresa['direccion'] ?? '' }}</p>
            <p>Tel: {{ $empresa['telefono'] ?? '-' }}</p>
        </div>
        <div class="col-right">
            <div class="title-box">{{ $tipoGuiaLabel }}</div>
            <div class="number-box">{{ $guia->numero_guia }}</div>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="section">
        <h3>Datos Generales</h3>
        <div class="kv"><strong>Fecha emision:</strong> {{ optional($guia->fecha_emision)->format('d/m/Y H:i') }}</div>
        <div class="kv"><strong>Fecha traslado:</strong> {{ optional($guia->fecha_traslado)->format('d/m/Y') }}</div>
        <div class="kv"><strong>Motivo traslado:</strong> {{ $guia->motivo_traslado_codigo }} - {{ $guia->motivo_traslado_descripcion }}</div>
        <div class="kv"><strong>Modalidad transporte:</strong> {{ $modalidadLabel }}</div>
        <div class="kv"><strong>Peso total:</strong> {{ number_format((float) $guia->peso_total, 3) }} {{ $guia->unidad_peso ?: 'KGM' }}</div>
        <div class="kv"><strong>Nro bultos:</strong> {{ $guia->numero_bultos ?: '-' }}</div>
        @if($guia->venta)
            <div class="kv"><strong>Comprobante relacionado:</strong> {{ $guia->venta->numero_comprobante }}</div>
        @endif
    </div>

    <div class="section">
        <h3>Destinatario</h3>
        <div class="kv"><strong>Tipo / Numero doc:</strong> {{ $guia->destinatario_tipo_doc }} - {{ $guia->destinatario_num_doc }}</div>
        <div class="kv"><strong>Razon social:</strong> {{ $guia->destinatario_razon_social }}</div>
    </div>

    <div class="section">
        <h3>Direcciones</h3>
        <div class="kv"><strong>Partida:</strong> [{{ $guia->partida_ubigeo ?: '-' }}] {{ $guia->partida_direccion }}</div>
        <div class="kv"><strong>Llegada:</strong> [{{ $guia->llegada_ubigeo ?: '-' }}] {{ $guia->llegada_direccion }}</div>
    </div>

    <div class="section">
        <h3>Transporte</h3>
        @if((string) $guia->modalidad_transporte === '01' || (string) $guia->tipo_documento === '31')
            <div class="kv"><strong>Transportista:</strong> {{ $guia->transportista_razon_social ?: '-' }}</div>
            <div class="kv"><strong>Doc transportista:</strong> {{ $guia->transportista_tipo_doc ?: '-' }} - {{ $guia->transportista_num_doc ?: '-' }}</div>
            <div class="kv"><strong>Registro MTC:</strong> {{ $guia->transportista_reg_mtc ?: '-' }}</div>
        @endif

        @if((string) $guia->modalidad_transporte === '02')
            <div class="kv"><strong>Conductor:</strong> {{ $guia->conductor_nombres ?: '-' }}</div>
            <div class="kv"><strong>Doc conductor:</strong> {{ $guia->conductor_tipo_doc ?: '-' }} - {{ $guia->conductor_num_doc ?: '-' }}</div>
            <div class="kv"><strong>Licencia:</strong> {{ $guia->conductor_licencia ?: '-' }}</div>
            <div class="kv"><strong>Placa principal:</strong> {{ $guia->vehiculo_placa ?: '-' }}</div>
            <div class="kv"><strong>Placa secundaria:</strong> {{ $guia->vehiculo_secundario_placa ?: '-' }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">#</th>
                <th style="width: 14%;">Codigo</th>
                <th>Descripcion</th>
                <th style="width: 12%;">Unidad</th>
                <th style="width: 14%;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse($guia->detalles as $i => $d)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $d->codigo ?: '-' }}</td>
                    <td>{{ $d->descripcion }}</td>
                    <td class="text-center">{{ $d->unidad ?: 'NIU' }}</td>
                    <td class="text-right">{{ number_format((float) $d->cantidad, 3) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center muted">Sin items</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($guia->observacion)
        <div class="section">
            <h3>Observacion</h3>
            <p style="margin: 0;">{{ $guia->observacion }}</p>
        </div>
    @endif

    <div class="footer">
        <div>Representacion impresa de la guia de remision electronica.</div>
        @if($guia->hash_cpe)
            <div>Hash: {{ $guia->hash_cpe }}</div>
        @endif
    </div>
</body>
</html>

