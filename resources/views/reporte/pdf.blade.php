<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ruta {{ $ruta['id'] }}</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .header { margin-bottom: 16px; border-bottom: 3px solid #2563eb; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: 700; color: #1d4ed8; }
        .subtitle { font-size: 12px; color: #475569; margin-top: 4px; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .grid td { padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: top; }
        .section-title { font-size: 13px; font-weight: 700; margin: 18px 0 8px; color: #0f172a; }
        table.table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .table th, .table td { border: 1px solid #cbd5e1; padding: 6px; }
        .table th { background: #e0ecff; text-align: left; }
        .text-right { text-align: right; }
        .totals td { font-weight: 700; background: #f8fafc; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-ok { background: #dcfce7; color: #166534; }
        .badge-bad { background: #fee2e2; color: #991b1b; }
        .badge-neutral { background: #e2e8f0; color: #334155; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte Operativo de Ruta #{{ $ruta['id'] }}</div>
        <div class="subtitle">{{ $empresa['razon_social'] }} | RUC {{ $empresa['ruc'] }}</div>
    </div>

    <table class="grid">
        <tr>
            <td><strong>Origen:</strong> {{ $ruta['origen'] }}</td>
            <td><strong>Destino:</strong> {{ $ruta['destino'] }}</td>
        </tr>
        <tr>
            <td><strong>Conductor:</strong> {{ $ruta['conductor'] ?: '-' }}</td>
            <td><strong>Estado:</strong> {{ $ruta['estado'] ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Tracto:</strong> {{ $ruta['unidad']['tracto'] ?: '-' }}</td>
            <td><strong>Trailer:</strong> {{ $ruta['unidad']['trailer'] ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Fecha inicio:</strong> {{ $ruta['fecha_inicio'] ?: '-' }}</td>
            <td><strong>Fecha fin:</strong> {{ $ruta['fecha_fin'] ?: '-' }}</td>
        </tr>
    </table>

    <div class="section-title">Resumen economico</div>
    <table class="table">
        <tr class="totals">
            <td>Ingresos del viaje</td>
            <td class="text-right">S/ {{ number_format((float) $ruta['totales']['ingresos'], 2) }}</td>
            <td>Utilidad real</td>
            <td class="text-right">S/ {{ number_format((float) $ruta['totales']['utilidad'], 2) }}</td>
        </tr>
        <tr class="totals">
            <td>Total viaticos</td>
            <td class="text-right">S/ {{ number_format((float) $ruta['totales']['viaticos'], 2) }}</td>
            <td>Total combustible</td>
            <td class="text-right">S/ {{ number_format((float) $ruta['totales']['combustible'], 2) }}</td>
        </tr>
        <tr class="totals">
            <td>Total peajes</td>
            <td class="text-right">S/ {{ number_format((float) $ruta['totales']['peajes'], 2) }}</td>
            <td>Total gastos</td>
            <td class="text-right">S/ {{ number_format((float) $ruta['totales']['gastos'], 2) }}</td>
        </tr>
    </table>

    <div class="section-title">Indicadores de rentabilidad</div>
    <table class="table">
        <tr>
            <td><strong>Margen del viaje</strong></td>
            <td class="text-right">
                @if(is_null($ruta['analisis']['margen_pct']))
                    N/D
                @else
                    {{ number_format((float) $ruta['analisis']['margen_pct'], 2) }}%
                @endif
            </td>
            <td><strong>Gasto vs ingreso</strong></td>
            <td class="text-right">
                @if(is_null($ruta['analisis']['gasto_vs_ingreso_pct']))
                    N/D
                @else
                    {{ number_format((float) $ruta['analisis']['gasto_vs_ingreso_pct'], 2) }}%
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Ganancia registrada</strong></td>
            <td class="text-right">S/ {{ number_format((float) $ruta['analisis']['utilidad_registrada'], 2) }}</td>
            <td><strong>Desviacion</strong></td>
            <td class="text-right">S/ {{ number_format((float) $ruta['analisis']['desviacion_utilidad'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>Caja chica</strong></td>
            <td class="text-right">S/ {{ number_format((float) $ruta['analisis']['caja_chica'], 2) }}</td>
            <td><strong>Saldo caja chica</strong></td>
            <td class="text-right">S/ {{ number_format((float) $ruta['analisis']['saldo_caja_chica'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>Clasificacion</strong></td>
            <td colspan="3">
                @php
                    $utilidad = (float) $ruta['totales']['utilidad'];
                    $badgeClass = $utilidad > 0 ? 'badge-ok' : ($utilidad < 0 ? 'badge-bad' : 'badge-neutral');
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $ruta['analisis']['clasificacion'] ?? 'N/D' }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Viaticos por concepto</div>
    <table class="table">
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Promedio</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ruta['viaticos_resumen'] as $item)
                <tr>
                    <td>{{ $item['servicio'] ?: '-' }}</td>
                    <td class="text-right">{{ number_format((float) $item['cantidad'], 0) }}</td>
                    <td class="text-right">S/ {{ number_format((float) $item['promedio'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format((float) $item['total'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No hay viaticos para consolidar.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Viaticos</div>
    <table class="table">
        <thead>
            <tr>
                <th>Servicio</th>
                <th>Fecha</th>
                <th>Factura</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ruta['viaticos'] as $viatico)
                <tr>
                    <td>{{ $viatico['nombre_servicio'] ?: '-' }}</td>
                    <td>{{ $viatico['fecha'] ?: '-' }}</td>
                    <td>{{ $viatico['numero_factura'] ?: '-' }}</td>
                    <td class="text-right">S/ {{ number_format((float) $viatico['importe'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No hay viaticos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Combustible</div>
    <table class="table">
        <thead>
            <tr>
                <th>Grifo</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th class="text-right">Galones</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ruta['combustibles'] as $combustible)
                <tr>
                    <td>{{ $combustible['grifo'] ?: '-' }}</td>
                    <td>{{ $combustible['fecha_hora'] ?: '-' }}</td>
                    <td>{{ $combustible['tipo_combustible'] ?: '-' }}</td>
                    <td class="text-right">{{ number_format((float) $combustible['galones'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format((float) $combustible['importe'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No hay consumos de combustible registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Peajes</div>
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ruta['peajes'] as $peaje)
                <tr>
                    <td>{{ $peaje['nombre'] ?: '-' }}</td>
                    <td>{{ $peaje['fecha_hora'] ?: '-' }}</td>
                    <td>{{ $peaje['comprobante'] ?: '-' }}</td>
                    <td class="text-right">S/ {{ number_format((float) $peaje['importe'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No hay peajes registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Observaciones</div>
    <p>{{ $ruta['observaciones'] ?: 'Sin observaciones registradas.' }}</p>
</body>
</html>
