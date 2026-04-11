<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $tipoNotaLabel }} {{ $nota->numero_comprobante }}</title>
<style>
@page { margin: 10mm; }
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
.wrap { border: 1px solid #1e3a5f; padding: 10px; min-height: 270mm; box-sizing: border-box; }
.top { border-top: 4px solid #15407d; margin-bottom: 8px; }
.header { width: 100%; border-collapse: collapse; }
.header td { vertical-align: top; }
.title { font-size: 18px; font-weight: bold; color: #15407d; margin-bottom: 5px; }
.doc-box { border: 2px solid #15407d; border-radius: 8px; overflow: hidden; width: 280px; float: right; }
.doc-box .head { background: #15407d; color: #fff; text-align: center; font-weight: bold; padding: 8px; font-size: 12px; }
.doc-box .body { text-align: center; padding: 10px; font-weight: bold; font-size: 18px; }
.block-table { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-top: 8px; }
.block { border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; padding: 8px; min-height: 90px; }
.block .h { font-size: 11px; color: #15407d; font-weight: bold; margin-bottom: 6px; letter-spacing: .5px; }
.label { font-weight: bold; color: #475569; }
.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
.items th { background: #15407d; color: #fff; border: 1px solid #b8cbe4; padding: 6px; font-size: 10px; }
.items td { border: 1px solid #d2dce8; padding: 6px; font-size: 10px; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.summary { width: 100%; border-collapse: collapse; margin-top: 10px; }
.letters { width: 62%; vertical-align: top; }
.totals { width: 38%; vertical-align: top; }
.box { border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; }
.box table { width: 100%; border-collapse: collapse; }
.box td { border-bottom: 1px solid #dbe4ef; padding: 7px 10px; font-size: 11px; }
.box tr:last-child td { border-bottom: 0; }
.final td { background: #15407d; color: #fff; font-weight: bold; font-size: 13px; }
.foot { margin-top: 12px; border-top: 1px solid #15407d; padding-top: 6px; font-size: 10px; color: #475569; text-align: center; }
</style>
</head>
<body>
<div class="wrap">
    <div class="top"></div>

    <table class="header">
        <tr>
            <td width="62%">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" width="90" alt="Logo">
                @endif
                <div class="title">{{ $empresa['razon_social'] }}</div>
                <div>RUC: {{ $empresa['ruc'] }}</div>
                <div>{{ $empresa['direccion'] }}</div>
                <div>Tel: {{ $empresa['telefono'] }}</div>
            </td>
            <td width="38%">
                <div class="doc-box">
                    <div class="head">{{ $tipoNotaLabel }}</div>
                    <div class="body">{{ $nota->numero_comprobante }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="block-table">
        <tr>
            <td width="50%">
                <div class="block">
                    <div class="h">DOCUMENTO AFECTADO</div>
                    <div><span class="label">Comprobante:</span> {{ $nota->numDocAfectado }}</div>
                    <div><span class="label">Tipo:</span> {{ $nota->tipDocAfectado }}</div>
                    <div><span class="label">Fecha nota:</span> {{ optional($nota->fecha_emision)->format('d/m/Y H:i') }}</div>
                </div>
            </td>
            <td width="50%">
                <div class="block">
                    <div class="h">DATOS DEL CLIENTE</div>
                    <div><span class="label">Cliente:</span> {{ $venta->nombre_cliente }}</div>
                    <div><span class="label">Documento:</span> {{ $venta->numero_documento_cliente ?: '-' }}</div>
                    <div><span class="label">Moneda:</span> {{ $moneda }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
        <tr>
            <th>#</th>
            <th>Codigo</th>
            <th>Descripcion</th>
            <th>Unidad</th>
            <th>Cant.</th>
            <th>V. Unit.</th>
            <th>Subtotal</th>
            <th>IGV</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($itemsDisplay as $d)
            <tr>
                <td class="text-center">{{ $d['index'] }}</td>
                <td class="text-center">{{ $d['codigo'] }}</td>
                <td>{{ $d['descripcion'] }}</td>
                <td class="text-center">{{ $d['unidad'] }}</td>
                <td class="text-right">{{ number_format($d['cantidad'], 2) }}</td>
                <td class="text-right">{{ $monedaSimbolo }} {{ number_format($d['valor_unitario'], 2) }}</td>
                <td class="text-right">{{ $monedaSimbolo }} {{ number_format($d['subtotal'], 2) }}</td>
                <td class="text-right">{{ ((float)$d['igv'] > 0) ? ($monedaSimbolo . ' ' . number_format($d['igv'], 2)) : '-' }}</td>
                <td class="text-right">{{ $monedaSimbolo }} {{ number_format($d['total'], 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="letters">
                <div><strong>Motivo:</strong> {{ $nota->codMotivo }} - {{ $nota->desMotivo }}</div>
                <div style="margin-top:8px;"><strong>Importe en letras:</strong> {{ $totalLetras }}</div>
            </td>
            <td class="totals">
                <div class="box">
                    <table>
                        <tr><td>Subtotal</td><td class="text-right">{{ $monedaSimbolo }} {{ number_format($totales['valor_venta'], 2) }}</td></tr>
                        <tr><td>IGV</td><td class="text-right">{{ $monedaSimbolo }} {{ number_format($totales['igv'], 2) }}</td></tr>
                        <tr class="final"><td>TOTAL</td><td class="text-right">{{ $monedaSimbolo }} {{ number_format($total, 2) }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="foot">
        Representacion impresa de la nota electronica generada en el sistema interno de {{ $empresa['razon_social'] }}.
    </div>
</div>
</body>
</html>
