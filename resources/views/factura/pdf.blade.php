<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $tipoDocumentoLabel }} {{ $venta->numero_comprobante }}</title>

<style>
@page {
    margin: 22px 28px 28px 28px;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #0f172a;
}

.top-line {
    border-top: 5px solid #0b3b78;
    margin-bottom: 12px;
}

.header {
    width: 100%;
}

.header td {
    vertical-align: top;
}

.company-name {
    font-size: 20px;
    font-weight: bold;
    color: #0b3b78;
}

.doc-box {
    border: 2px solid #0b3b78;
    border-radius: 10px;
    text-align: center;
    width: 230px;
    float: right;
}

.doc-box-head {
    background: #0b3b78;
    color: #fff;
    font-weight: bold;
    padding: 6px;
}

.doc-box-number {
    font-weight: bold;
    font-size: 16px;
    padding: 6px;
}

.block {
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 10px;
    background: #f8fafc;
}

.block-title {
    font-size: 10px;
    font-weight: bold;
    color: #0b3b78;
    margin-bottom: 6px;
}

.label {
    font-weight: bold;
    color: #64748b;
}

.items {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
}

.items th {
    background: #0b3b78;
    color: #fff;
    padding: 6px;
    font-size: 10px;
}

.items td {
    border: 1px solid #cbd5e1;
    padding: 6px;
    font-size: 10px;
}

.text-center { text-align: center; }
.text-right { text-align: right; }

/* 🔥 TOTALES */
.totals-wrap {
    margin-top: 15px;
    width: 100%;
}

.total-letters {
    width: 60%;
    font-size: 10px;
    padding-top: 15px;
}

.totals {
    width: 280px;
    float: right;
    margin-top: 5px;
    border: 2px solid #0b3b78;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
}

.totals table {
    width: 100%;
    border-collapse: collapse;
}

.totals td {
    padding: 10px 12px;
    font-size: 11px;
}

.label-total {
    width: 65%;
    font-weight: 600;
}

.amount {
    width: 35%;
    text-align: right;
    font-weight: 600;
}

.totals tr:nth-child(1),
.totals tr:nth-child(2) {
    background: #f1f5f9;
}

.total-final {
    background: #0b3b78;
    color: #fff;
    font-weight: bold;
    font-size: 12px;
}

.total-final td {
    padding: 12px;
}

.footer {
    margin-top: 30px;
    border-top: 2px solid #0b3b78;
    padding-top: 10px;
    font-size: 9px;
}
</style>
</head>

<body>

<div class="top-line"></div>

<table class="header">
<tr>
<td width="60%">
    @if($logoBase64)
        <img src="{{ $logoBase64 }}" width="90">
    @endif

    <div class="company-name">{{ $empresa['razon_social'] }}</div>
    <div>
        RUC: {{ $empresa['ruc'] }}<br>
        {{ $empresa['direccion'] }}<br>
        Tel: {{ $empresa['telefono'] }}
    </div>
</td>

<td width="40%">
    <div class="doc-box">
        <div class="doc-box-head">{{ $tipoDocumentoLabel }}</div>
        <div class="doc-box-number">{{ $venta->numero_comprobante }}</div>
    </div>
</td>
</tr>
</table>

<br>

<table width="100%">
<tr>
<td width="50%">
    <div class="block">
        <div class="block-title">Cliente</div>
        <div><span class="label">Nombre:</span> {{ $venta->nombre_cliente }}</div>
        <div><span class="label">Documento:</span> {{ $venta->numero_documento_cliente }}</div>
    </div>
</td>

<td width="50%">
    <div class="block">
        <div class="block-title">Comprobante</div>
        <div><span class="label">Fecha:</span> {{ optional($venta->fecha_emision)->format('d/m/Y H:i:s') }}</div>
        <div><span class="label">Moneda:</span> {{ strtoupper($venta->moneda) }}</div>
    </div>
</td>
</tr>
</table>

<!-- ITEMS -->
<table class="items">
<thead>
<tr>
<th>#</th>
<th>Cod</th>
<th>Descripción</th>
<th>Unid</th>
<th>Cant</th>
<th>V.Unit</th>
<th>Subt</th>
<th>IGV</th>
<th>Total</th>
</tr>
</thead>

<tbody>
@foreach($venta->detalles as $i => $d)
<tr>
<td class="text-center">{{ $i+1 }}</td>
<td class="text-center">{{ $d->codigo_producto }}</td>
<td>{{ $d->descripcion }}</td>
<td class="text-center">{{ $d->unidad }}</td>
<td class="text-right">{{ number_format($d->cantidad,2) }}</td>
<td class="text-right">{{ $monedaSimbolo }} {{ number_format($d->valor_unitario,2) }}</td>
<td class="text-right">{{ $monedaSimbolo }} {{ number_format($d->subtotal,2) }}</td>
<td class="text-right">{{ $monedaSimbolo }} {{ number_format($d->igv,2) }}</td>
<td class="text-right">{{ $monedaSimbolo }} {{ number_format($d->total,2) }}</td>
</tr>
@endforeach
</tbody>
</table>

<!-- TOTALES -->
<table class="totals-wrap">
<tr>

<td class="total-letters">
    <strong>SON:</strong><br>
    {{ $totalLetras }}
</td>

<td>
    <div class="totals">
        <table>
            <tr>
                <td class="label-total">Op. Gravadas</td>
                <td class="amount">{{ $monedaSimbolo }} {{ number_format($subtotal,2) }}</td>
            </tr>

            <tr>
                <td class="label-total">IGV (18%)</td>
                <td class="amount">{{ $monedaSimbolo }} {{ number_format($igv,2) }}</td>
            </tr>

            <tr class="total-final">
                <td>TOTAL A PAGAR</td>
                <td class="amount">{{ $monedaSimbolo }} {{ number_format($total,2) }}</td>
            </tr>
        </table>
    </div>
</td>

</tr>
</table>

<div class="footer">
Documento generado por {{ $empresa['razon_social'] }}
</div>

</body>
</html>