<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotizacion {{ $numeroCotizacion }}</title>
    <style>
        @page {
            margin: 18px 34px 28px 34px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111;
            font-size: 11px;
            line-height: 1.38;
        }

        .page {
            width: 100%;
        }

        .header-band {
            width: 100%;
            border-top: 4px solid #1f4f86;
            margin-bottom: 12px;
            padding-top: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo-cell {
            width: 22%;
        }

        .logo {
            max-width: 135px;
            max-height: 72px;
        }

        .title-cell {
            width: 48%;
            text-align: center;
        }

        .quote-box-cell {
            width: 30%;
            text-align: right;
        }

        .company-name {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: #1f2d3d;
            margin-top: 6px;
        }

        .quote-box {
            display: inline-block;
            min-width: 180px;
            border: 2px solid #1f4f86;
            padding: 10px 12px;
            text-align: center;
        }

        .quote-box-title {
            font-size: 13px;
            font-weight: 700;
            color: #1f4f86;
            margin-bottom: 6px;
        }

        .quote-box-number {
            font-size: 18px;
            font-weight: 700;
            color: #111;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .meta td {
            padding: 4px 0;
            vertical-align: top;
        }

        .meta-label {
            width: 90px;
            font-size: 12px;
            font-weight: 700;
        }

        .meta-value {
            font-size: 12px;
            font-weight: 700;
        }

        .meta-secondary {
            font-size: 12px;
            font-weight: 700;
            margin-top: 2px;
        }

        .divider {
            border-top: 1px solid #8d99a6;
            margin: 12px 0 10px;
        }

        .intro {
            font-size: 11px;
            margin-bottom: 8px;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .items th,
        .items td {
            border: 1px solid #1b1b1b;
            padding: 6px 6px;
            vertical-align: top;
        }

        .items thead th {
            background: #3b78b3;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .main-row {
            font-weight: 700;
        }

        .main-row td {
            background: #f4f4f4;
        }

        .detail-row td {
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .detail-description {
            padding-left: 20px !important;
        }

        .detail-intro {
            font-style: italic;
            color: #333;
        }

        .detail-bullet {
            padding-left: 24px !important;
            font-style: italic;
        }

        .igv-note {
            margin-top: 10px;
            font-size: 11px;
            font-weight: 700;
            font-style: italic;
        }

        .section-title {
            margin-top: 12px;
            margin-bottom: 6px;
            font-size: 11px;
            font-weight: 700;
        }

        .notes {
            margin: 4px 0 0 18px;
            padding: 0;
        }

        .notes li {
            margin-bottom: 4px;
        }

        .footer {
            margin-top: 20px;
        }

        .footer-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        .footer-grid td {
            vertical-align: top;
        }

        .payment-box {
            border: 1px solid #7aa6d8;
            padding: 8px 12px;
            width: 100%;
        }

        .payment-title {
            font-size: 11px;
            font-weight: 700;
            color: #1f4f86;
            margin-bottom: 6px;
        }

        .payment-row {
            margin-bottom: 4px;
            font-size: 10px;
            font-weight: 700;
        }

        .payment-bullet {
            display: inline-block;
            width: 10px;
            color: #111;
        }

        .signature-block {
            text-align: center;
            padding-left: 18px;
            font-size: 10px;
        }

        .signature-line {
            width: 160px;
            border-top: 1px solid #111;
            margin: 18px auto 6px;
        }

        .signature-role {
            font-size: 11px;
            font-weight: 700;
        }

        .signature-name {
            margin-top: 4px;
            font-size: 10px;
            font-weight: 700;
        }

        .company-strip {
            margin-top: 10px;
            border-top: 4px double #1f4f86;
            padding-top: 8px;
            text-align: center;
            font-size: 10px;
            color: #1f2937;
        }

        .company-strip a {
            color: #1d4ed8;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header-band"></div>

        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo" class="logo">
                    @endif
                </td>
                <!-- <td class="title-cell">
                    <div class="company-name">{{ $empresa['razon_social'] }}</div>
                </td> -->
                <td class="quote-box-cell">
                    <div class="quote-box">
                        <div class="quote-box-title">COTIZACION </div>
                        <div class="quote-box-number">N° {{ $numeroCotizacion }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="meta">
            <tr>
                <td class="meta-label">SEÑORES:</td>
                <td class="meta-value">
                    {{ $cotizacion->cliente?->razon_social ?? 'CLIENTE' }}
                    @if($cotizacion->cliente?->num_doc)
                        <div class="meta-secondary">{{ $tipoDocumentoCliente }}: {{ $cotizacion->cliente->num_doc }}</div>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="meta-label">DE:</td>
                <td class="meta-value">
                    {{ $empresa['razon_social'] }}
                </td>
            </tr>
            <tr>
                <td></td>
                <td class="meta-secondary">GERENTE DE {{ mb_strtoupper($empresa['razon_social'], 'UTF-8') }}</td>
            </tr>
            <tr>
                <td class="meta-label">ASUNTO:</td>
                <td class="meta-value">{{ mb_strtoupper($cotizacion->asunto ?: 'COTIZACION DE SERVICIOS', 'UTF-8') }}</td>
            </tr>
            <tr>
                <td class="meta-label">FECHA:</td>
                <td class="meta-value">{{ $fechaTexto }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="intro">
            {{ $introTexto }}
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 7%;">ITEM</th>
                    <th style="width: 7%;">cant.</th>
                    <th>descripcion</th>
                    <th style="width: 11%;">precio por unidad (S/.)</th>
                    <th style="width: 11%;">precio parcial (S/.)</th>
                    <th style="width: 12%;">subtotal (S/.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cotizacion->detalles as $index => $detalle)
                    <tr class="main-row">
                        <td class="text-center">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($detalle->cantidad, 2, '.', ''), '0'), '.') }}</td>
                        <td>{{ mb_strtoupper($detalle->nombre_item, 'UTF-8') }}</td>
                        <td class="text-right">{{ number_format((float) $detalle->precio, 2) }}</td>
                        <td class="text-right">{{ number_format((float) $detalle->subtotal, 2) }}</td>
                        <td class="text-right">{{ number_format((float) $detalle->subtotal, 2) }}</td>
                    </tr>

                    @if(is_array($detalle->detalle_servicio) && count($detalle->detalle_servicio))
                        <tr class="detail-row">
                            <td></td>
                            <td></td>
                            <td class="detail-description detail-intro">Consistente:</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        @foreach($detalle->detalle_servicio as $paso)
                            <tr class="detail-row">
                                <td></td>
                                <td></td>
                                <td class="detail-bullet">○ {{ $paso }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="font-weight: 700;">{{ $totalLetras }}</td>
                    <td colspan="2" class="text-center" style="font-weight: 700;">TOTAL</td>
                    <td class="text-right" style="font-weight: 700; font-size: 16px;">{{ number_format((float) $cotizacion->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="igv-note">
            {{ $cotizacion->igv > 0 ? 'LA PRESENTE COTIZACION INCLUYE I.G.V.' : 'LA PRESENTE COTIZACION NO INCLUYE I.G.V.' }}
        </div>

        @if($cotizacion->descripcion_general)
            <div class="section-title">Descripcion general:</div>
            <div>{{ $cotizacion->descripcion_general }}</div>
        @endif

        @if($notas->isNotEmpty())
            <div class="section-title">Nota:</div>
            <ul class="notes">
                @foreach($notas as $nota)
                    <li>{{ $nota }}</li>
                @endforeach
            </ul>
        @endif

        <div class="footer">
            <table class="footer-grid">
                <tr>
                    <td style="width: 58%;">
                        @if($mediosPago->isNotEmpty())
                            <div class="payment-box">
                                <div class="payment-title">Medios de pago</div>
                                @foreach($mediosPago as $medio)
                                    <div class="payment-row" style="color: {{ $medio['color'] ?? '#111111' }};">
                                        <span class="payment-bullet">&#9633;</span>
                                        {{ $medio['label'] }}: {{ $medio['detalle'] }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td style="width: 42%;">
                        <div class="signature-block">
                            <div class="signature-line"></div>
                            <div class="signature-role">{{ $empresa['gerente_cargo'] }}</div>
                            <div class="signature-name">{{ $empresa['gerente_nombre'] }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="company-strip">
                <div>
                    {{ $empresa['direccion'] }},
                    {{ $empresa['distrito'] }},
                    {{ $empresa['provincia'] }},
                    {{ $empresa['departamento'] }}
                </div>
                @if($empresa['telefono'])
                    <div>Cel: {{ $empresa['telefono'] }}</div>
                @endif
                @if(!empty($empresa['emails']))
                    <div>
                        e-mail:
                        @foreach($empresa['emails'] as $email)
                            <span>{{ $email }}</span>@if(!$loop->last) | @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
