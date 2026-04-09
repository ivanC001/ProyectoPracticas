<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $tipoDocumentoLabel }} {{ $venta->numero_comprobante }}</title>
<style>
@page {
    size: A4;
    margin: 6mm;
}

body {
    margin: 0;
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
    color: #111827;
}

.page {
    border: 1px solid #111827;
    min-height: 284mm;
    box-sizing: border-box;
    padding: 6px 8px;
}

.top-line {
    height: 4px;
    background: #0f3f82;
    margin-bottom: 8px;
}

.header {
    width: 100%;
    border-collapse: collapse;
}

.header td {
    vertical-align: top;
}

.empresa-title {
    color: #0f3f82;
    font-size: 15px;
    font-weight: 700;
    margin: 3px 0 5px;
}

.empresa-info {
    line-height: 1.4;
    color: #334155;
    font-size: 10px;
}

.doc-box {
    border: 2px solid #0f3f82;
    border-radius: 8px;
    overflow: hidden;
}

.doc-box-head {
    background: #0f3f82;
    color: #fff;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 8px;
    letter-spacing: .5px;
}

.doc-box-body {
    text-align: center;
    padding: 8px 6px;
}

.doc-number {
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 1px;
}

.section-box {
    margin-top: 7px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 7px 8px;
    background: #f8fafc;
}

.section-title {
    color: #0f3f82;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .8px;
    margin-bottom: 5px;
}

.two-col {
    width: 100%;
    border-collapse: collapse;
}

.two-col td {
    vertical-align: top;
}

.line {
    margin-bottom: 4px;
}

.line strong {
    color: #475569;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}

.items-table th {
    background: #0f3f82;
    color: #fff;
    border: 1px solid #9fb3cf;
    padding: 5px 4px;
    font-size: 9px;
    text-transform: uppercase;
}

.items-table td {
    border: 1px solid #cbd5e1;
    padding: 5px 4px;
    font-size: 9px;
}

.items-table tr:nth-child(even) td {
    background: #f8fafc;
}

.text-center { text-align: center; }
.text-right { text-align: right; }

.summary-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}

.summary-left {
    width: 58%;
    vertical-align: top;
    padding-right: 8px;
}

.summary-right {
    width: 42%;
    vertical-align: top;
}

.letters-title {
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 4px;
}

.letters-value {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #1f2937;
}

.totals-box {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    overflow: hidden;
}

.totals-box table {
    width: 100%;
    border-collapse: collapse;
}

.totals-box td {
    border-bottom: 1px solid #dbe2ec;
    padding: 6px 8px;
    font-size: 10px;
}

.totals-box tr:last-child td {
    border-bottom: 0;
}

.total-final td {
    background: #0f3f82;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
}

.extra-box {
    margin-top: 8px;
    border: 1px solid #9ca3af;
}

.extra-box .head {
    background: #fef08a;
    border-bottom: 1px solid #9ca3af;
    padding: 4px 6px;
    font-weight: 700;
}

.extra-box .body {
    padding: 5px 6px;
}

.extra-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
}

.extra-table td, .extra-table th {
    border: 1px solid #cbd5e1;
    padding: 4px;
}

.security {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}

.security td {
    vertical-align: top;
}

.hash-box {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 6px;
    background: #f8fafc;
}

.hash-title {
    color: #0f3f82;
    font-weight: 700;
    margin-bottom: 3px;
}

.hash-value {
    word-break: break-all;
}

.qr-wrap {
    text-align: right;
}

.qr-box {
    display: inline-block;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 5px;
    background: #fff;
}

.footer-note {
    margin-top: 8px;
    border: 1px solid #9ca3af;
    padding: 5px 6px;
    text-align: center;
    font-size: 10px;
    font-style: italic;
}
</style>
</head>
<body>
<div class="page">
    <div class="top-line"></div>

    <table class="header">
        <tr>
            <td width="66%">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" width="85" alt="Logo">
                @endif
                <div class="empresa-title">{{ $empresa['razon_social'] }}</div>
                <div class="empresa-info">
                    RUC: {{ $empresa['ruc'] }}<br>
                    {{ $empresa['direccion'] }}<br>
                    Tel: {{ $empresa['telefono'] }}
                </div>
            </td>
            <td width="34%">
                <div class="doc-box">
                    <div class="doc-box-head">{{ $tipoDocumentoLabel }}</div>
                    <div class="doc-box-body">
                        <div class="doc-number">{{ $venta->numero_comprobante }}</div>
                        <div>RUC: {{ $empresa['ruc'] }}</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="two-col">
        <tr>
            <td width="58%">
                <div class="section-box">
                    <div class="section-title">DATOS DEL CLIENTE</div>
                    <div class="line"><strong>Senor(es):</strong> {{ $venta->nombre_cliente }}</div>
                    <div class="line"><strong>Documento:</strong> {{ $venta->numero_documento_cliente ?: '-' }}</div>
                    <div class="line"><strong>Tipo doc:</strong> {{ $venta->tipo_documento_cliente ?: '-' }}</div>
                </div>
            </td>
            <td width="42%">
                <div class="section-box">
                    <div class="section-title">DATOS DEL COMPROBANTE</div>
                    <div class="line"><strong>Fecha de emision:</strong> {{ optional($venta->fecha_emision)->format('d/m/Y H:i') }}</div>
                    <div class="line"><strong>Tipo de moneda:</strong> {{ strtoupper($venta->moneda) }}</div>
                    <div class="line">
                        <strong>Forma de pago:</strong>
                        {{ $formaPagoLabel ?? (strtolower((string) ($venta->forma_pago ?? 'contado')) === 'credito' ? 'Credito' : 'Contado') }}
                    </div>
                    @if(!empty($venta->observacion))
                        <div class="line"><strong>Observacion:</strong> {{ $venta->observacion }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="4%">#</th>
                <th width="14%">Codigo</th>
                <th width="35%">Descripcion</th>
                <th width="8%">Unidad</th>
                <th width="8%">Cant.</th>
                <th width="10%">V. Unit.</th>
                <th width="10%">Subtotal</th>
                <th width="6%">IGV</th>
                <th width="10%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detallesRender as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $item['codigo_producto'] }}</td>
                    <td>
                        {{ $item['descripcion'] }}
                        @if(($item['tipo_item'] ?? 'producto') === 'servicio')
                            <div><strong>Servicio</strong></div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item['unidad'] }}</td>
                    <td class="text-right">{{ number_format($item['cantidad'], 2) }}</td>
                    <td class="text-right">{{ $monedaSimbolo }} {{ number_format($item['valor_unitario'], 2) }}</td>
                    <td class="text-right">{{ $monedaSimbolo }} {{ number_format($item['subtotal'], 2) }}</td>
                    <td class="text-right">{{ $item['aplica_igv'] ? ($monedaSimbolo . ' ' . number_format($item['igv'], 2)) : '-' }}</td>
                    <td class="text-right">{{ $monedaSimbolo }} {{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="summary-left">
                <div class="letters-title">SON:</div>
                <div class="letters-value">{{ $totalLetras }}</div>
                @if(!empty($pdfLegends))
                    <div style="margin-top:6px;">
                        @foreach($pdfLegends as $legend)
                            <div>{{ $legend }}</div>
                        @endforeach
                    </div>
                @endif
            </td>
            <td class="summary-right">
                <div class="totals-box">
                    <table>
                        <tr>
                            <td>Sub Total Ventas</td>
                            <td class="text-right">{{ $monedaSimbolo }} {{ number_format($subtotal, 2) }}</td>
                        </tr>
                        @if(($totales['exoneradas'] ?? 0) > 0)
                            <tr>
                                <td>Op. Exoneradas</td>
                                <td class="text-right">{{ $monedaSimbolo }} {{ number_format($totales['exoneradas'], 2) }}</td>
                            </tr>
                        @endif
                        @if(($totales['inafectas'] ?? 0) > 0)
                            <tr>
                                <td>Op. Inafectas</td>
                                <td class="text-right">{{ $monedaSimbolo }} {{ number_format($totales['inafectas'], 2) }}</td>
                            </tr>
                        @endif
                        @if(($totales['exportacion'] ?? 0) > 0)
                            <tr>
                                <td>Op. Exportacion</td>
                                <td class="text-right">{{ $monedaSimbolo }} {{ number_format($totales['exportacion'], 2) }}</td>
                            </tr>
                        @endif
                        @if($igv > 0)
                            <tr>
                                <td>IGV</td>
                                <td class="text-right">{{ $monedaSimbolo }} {{ number_format($igv, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="total-final">
                            <td>TOTAL</td>
                            <td class="text-right">{{ $monedaSimbolo }} {{ number_format($total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    @if($venta->detraccion_aplica)
        <div class="extra-box">
            <div class="head">Informacion de la detraccion</div>
            <div class="body">
                <table class="extra-table">
                    <tr>
                        <td width="19%"><strong>Bien o Servicio</strong></td>
                        <td width="53%">{{ $venta->detraccion_codigo }} - {{ data_get($detraccionMeta, 'descripcion', 'Servicio sujeto a detraccion') }}</td>
                        <td width="14%"><strong>Porcentaje</strong></td>
                        <td width="14%" class="text-right">{{ number_format((float) $venta->detraccion_porcentaje, 2) }}%</td>
                    </tr>
                    <tr>
                        <td><strong>Medio de pago</strong></td>
                        <td>{{ $venta->detraccion_medio_pago ?: '001' }} - Deposito en cuenta</td>
                        <td><strong>Monto detraccion</strong></td>
                        <td class="text-right">{{ $monedaSimbolo }} {{ number_format((float) $venta->detraccion_monto, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nro. cta. Banco Nacion</strong></td>
                        <td colspan="3">{{ $venta->detraccion_cuenta }}</td>
                    </tr>
                </table>
            </div>
        </div>
    @endif

    @if(strtolower((string) $venta->forma_pago) === 'credito')
        <div class="extra-box">
            <div class="head">Informacion del credito</div>
            <div class="body">
                <table class="extra-table">
                    <tr>
                        <td width="28%"><strong>Monto neto pendiente de pago</strong></td>
                        <td width="22%" class="text-right">{{ $monedaSimbolo }} {{ number_format((float) $venta->credito_monto_pendiente, 2) }}</td>
                        <td width="24%"><strong>Total de cuotas</strong></td>
                        <td width="26%" class="text-right">{{ (int) $venta->credito_total_cuotas }}</td>
                    </tr>
                </table>
                <table class="extra-table" style="margin-top:4px;">
                    <thead>
                        <tr>
                            <th width="15%">Nro Cuota</th>
                            <th width="30%">Fec. Venc.</th>
                            <th width="55%">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($cuotasPreview ?? []) as $cuota)
                            <tr>
                                <td class="text-center">{{ $cuota['nro'] }}</td>
                                <td class="text-center">{{ optional($cuota['fecha'])->format('d/m/Y') ?: '-' }}</td>
                                <td class="text-right">{{ $monedaSimbolo }} {{ number_format((float) $cuota['monto'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center">1</td>
                                <td class="text-center">{{ optional($venta->credito_fecha_vencimiento)->format('d/m/Y') ?: '-' }}</td>
                                <td class="text-right">{{ $monedaSimbolo }} {{ number_format((float) $venta->credito_monto_pendiente, 2) }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <table class="security">
        <tr>
            <td width="73%">
                <div class="hash-box">
                    <div class="hash-title">Codigo Hash / Firma Digital</div>
                    <div class="hash-value">{{ $hashCpe ?: 'PENDIENTE DE GENERACION' }}</div>
                </div>
            </td>
            <td width="27%" class="qr-wrap">
                @if(!empty($qrDataUri))
                    <div class="qr-box">
                        <img src="{{ $qrDataUri }}" alt="QR SUNAT" width="92" height="92">
                        <div class="text-center">QR SUNAT</div>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Esta es una representacion impresa de la factura electronica, generada en el Sistema de SUNAT. Puede verificarla utilizando su clave SOL.
    </div>
</div>
</body>
</html>
