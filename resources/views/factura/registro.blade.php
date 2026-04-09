@php
    $igvCatalog = config('sunat_igv.catalog', []);
    $igvGroups = [
        'gravada' => 'Gravadas',
        'exonerada' => 'Exoneradas',
        'inafecta' => 'Inafectas',
        'gratuita' => 'Gratuitas',
        'exportacion' => 'Exportacion',
    ];
    $detraccionCatalog = config('sunat_detraccion.servicios', []);
@endphp

<style>
.factura-form .section-title { font-weight: 700; color: #0b3b78; margin-bottom: 10px; }
.factura-form .summary-card { max-height: 240px; overflow-y: auto; }
.factura-form .igv-info { border: 1px solid #d7e1ee; border-radius: 6px; background: #f8fbff; padding: 8px 10px; font-size: 12px; }
.factura-form .chip { display: inline-block; font-size: 11px; font-weight: 700; border-radius: 999px; padding: 2px 8px; margin-right: 6px; background: #dbeafe; color: #1e3a8a; }
.factura-form .box-soft { border: 1px solid #d6e0ec; border-radius: 8px; background: #f8fbff; padding: 10px; }
.factura-form .rules-box { border-radius: 8px; padding: 10px 12px; font-size: 12px; border: 1px dashed #cbd5e1; background: #f8fafc; color: #334155; }
.factura-form .rules-box strong { color: #0f172a; }
</style>

<form id="formRegistroFactura" class="factura-form">
@csrf
<div id="backendValidationBox" class="alert alert-danger d-none"></div>

<div class="alert alert-info py-2">
    Factura: RUC obligatorio. Boleta: puede emitirse con DNI o RUC; si supera S/ 500 se exige DNI.
</div>
<div id="reglasOperacionHint" class="rules-box mb-2"></div>

<div class="row">
    <div class="col-md-3">
        <label for="tipo_documento">Tipo comprobante</label>
        <select class="form-control" id="tipo_documento"><option value="01">Factura</option><option value="03">Boleta</option></select>
        <small id="tipoComprobanteHelper" class="form-text text-muted"></small>
    </div>
    <div class="col-md-3">
        <label for="fecha_emision">Fecha emision</label>
        <input type="datetime-local" class="form-control" id="fecha_emision">
        <small class="form-text text-muted">Maximo 2 dias anteriores.</small>
    </div>
    <div class="col-md-2">
        <label for="moneda">Moneda</label>
        <select class="form-control" id="moneda"><option value="PEN">Soles</option><option value="USD">Dolares</option></select>
    </div>
    <div class="col-md-2">
        <label for="forma_pago">Forma pago</label>
        <select class="form-control" id="forma_pago"><option value="contado">Contado</option><option value="credito">Credito</option></select>
    </div>
    <div class="col-md-2">
        <label for="observacion">Observacion</label>
        <input type="text" class="form-control" id="observacion" placeholder="Opcional">
    </div>
</div>

<div id="creditoPanel" class="box-soft mt-2 d-none">
    <div class="row">
        <div class="col-md-4"><label for="credito_cuotas">Cuotas</label><input type="number" min="1" max="36" value="1" class="form-control" id="credito_cuotas"></div>
        <div class="col-md-4"><label for="credito_fecha_vencimiento">Fec. vencimiento</label><input type="date" class="form-control" id="credito_fecha_vencimiento"></div>
        <div class="col-md-4"><label for="credito_monto_pendiente">Monto pendiente</label><input type="number" step="0.01" min="0" class="form-control" id="credito_monto_pendiente" placeholder="Auto"></div>
    </div>
</div>

<hr>
<h6 class="section-title">Cliente</h6>
<div class="row">
    <div class="col-md-2"><label for="cliente_tipo_doc">Tipo doc</label><select class="form-control" id="cliente_tipo_doc"><option value="1">DNI</option><option value="0">Sin documento</option><option value="6">RUC</option></select></div>
    <div class="col-md-3"><label for="cliente_num_doc">Numero doc</label><input type="text" class="form-control" id="cliente_num_doc"><small id="clienteEstado" class="form-text text-muted"></small></div>
    <div class="col-md-7"><label for="cliente_razon_social">Nombre / Razon social</label><input type="text" class="form-control" id="cliente_razon_social"></div>
</div>
<div class="row mt-2">
    <div class="col-md-4"><input type="text" class="form-control" id="cliente_direccion" placeholder="Direccion"></div>
    <div class="col-md-4"><input type="email" class="form-control" id="cliente_email" placeholder="Email"></div>
    <div class="col-md-4"><input type="text" class="form-control" id="cliente_telefono" placeholder="Telefono"></div>
</div>

<hr>
<h6 class="section-title">Items</h6>
<div class="row">
    <div class="col-md-2"><label for="tipoItemSelector">Tipo item</label><select class="form-control" id="tipoItemSelector"><option value="producto">Producto</option><option value="servicio">Servicio</option></select></div>
    <div class="col-md-3"><label for="catalogoItems">Item</label><select class="form-control" id="catalogoItems"></select></div>
    <div class="col-md-1"><label for="cantidadItem">Cant.</label><input type="number" class="form-control" id="cantidadItem" value="1" min="0.01" step="0.01"></div>
    <div class="col-md-1"><label for="descuentoItem">Desc.</label><input type="number" class="form-control" id="descuentoItem" value="0" min="0" step="0.01"></div>
    <div class="col-md-2"><label for="precioUnitario">V. unitario</label><input type="number" class="form-control" id="precioUnitario" step="0.01" min="0"></div>
    <div class="col-md-2">
        <label for="tipoAfectacionIgv">Afect. IGV</label>
        <select class="form-control" id="tipoAfectacionIgv">
            @foreach($igvGroups as $groupKey => $groupLabel)
                @php $opts = collect($igvCatalog)->filter(fn($item) => ($item['group'] ?? null) === $groupKey); @endphp
                @if($opts->isNotEmpty())
                    <optgroup label="{{ $groupLabel }}">
                        @foreach($opts as $code => $item)
                            <option value="{{ $code }}" @selected($code === '10')>{{ $code }} - {{ $item['label'] }}</option>
                        @endforeach
                    </optgroup>
                @endif
            @endforeach
        </select>
    </div>
    <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-success btn-block" onclick="agregarItem()"><i class="fas fa-plus"></i></button></div>
</div>

<div class="row mt-2"><div class="col-md-12"><div class="igv-info" id="tipoAfectacionInfo"></div></div></div>

<div class="table-responsive mt-3">
    <table class="table table-bordered table-sm">
        <thead class="thead-dark"><tr><th>Tipo</th><th>Item</th><th>IGV</th><th>Cant</th><th>Precio</th><th>Desc</th><th>Subtotal</th><th>IGV</th><th>Total</th><th></th></tr></thead>
        <tbody id="tablaItems"></tbody>
    </table>
</div>

<div id="detraccionPanel" class="box-soft mt-2 d-none">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Detraccion SUNAT</strong>
        <div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="detraccion_aplica"><label class="custom-control-label" for="detraccion_aplica">Aplicar</label></div>
    </div>
    <div id="detraccionHint" class="alert alert-secondary py-2 mb-2"></div>
    <div class="row">
        <div class="col-md-5">
            <label for="detraccion_codigo">Codigo</label>
            <select class="form-control" id="detraccion_codigo">
                @foreach($detraccionCatalog as $code => $meta)
                    <option value="{{ $code }}">{{ $code }} - {{ $meta['descripcion'] }} ({{ number_format((float) $meta['porcentaje'], 2) }}%)</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><label for="detraccion_porcentaje">%</label><input type="number" class="form-control" id="detraccion_porcentaje" readonly></div>
        <div class="col-md-2"><label for="detraccion_base">Base serv.</label><input type="number" class="form-control" id="detraccion_base" readonly></div>
        <div class="col-md-3"><label for="detraccion_cuenta">Cuenta BN</label><input type="text" class="form-control" id="detraccion_cuenta" value="{{ config('sunat_detraccion.cuenta_bn_default', '') }}"></div>
    </div>
    <div class="row mt-2">
        <div class="col-md-3">
            <label for="detraccion_monto">Monto detraccion</label>
            <input type="number" class="form-control" id="detraccion_monto" min="0" step="0.01">
        </div>
        <div class="col-md-9 d-flex align-items-end">
            <small class="text-muted">Puedes ajustar el monto manualmente si lo requieres.</small>
        </div>
    </div>
</div>

<div class="row justify-content-end mt-2">
    <div class="col-md-6">
        <div class="summary-card border rounded p-2">
            <table class="table table-sm table-bordered mb-0">
                <tbody>
                    <tr><th>Op. Gravadas</th><td class="text-right" id="resumenGravadas">S/ 0.00</td></tr>
                    <tr><th>Op. Exoneradas</th><td class="text-right" id="resumenExoneradas">S/ 0.00</td></tr>
                    <tr><th>Op. Inafectas</th><td class="text-right" id="resumenInafectas">S/ 0.00</td></tr>
                    <tr><th>Op. Exportacion</th><td class="text-right" id="resumenExportacion">S/ 0.00</td></tr>
                    <tr><th>Op. Gratuitas</th><td class="text-right" id="resumenGratuitas">S/ 0.00</td></tr>
                    <tr><th>Total servicios</th><td class="text-right" id="resumenServicios">S/ 0.00</td></tr>
                    <tr><th>IGV</th><td class="text-right" id="resumenIgv">S/ 0.00</td></tr>
                    <tr class="table-primary"><th>Total</th><td class="text-right font-weight-bold" id="totalGeneral">S/ 0.00</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</form>

@push('scripts')
<script>
window.__FACTURA_REGISTRO_CONFIG__ = {
    igvCatalog: @json(config('sunat_igv.catalog', [])),
    detraccionCatalog: @json(config('sunat_detraccion.servicios', [])),
    detraccionMedioPago: @json(config('sunat_detraccion.medio_pago_default', '001')),
    detraccionMinimoServicios: @json(config('sunat_detraccion.monto_minimo_servicios', 700))
};
</script>
<script src="{{ asset('assets/js/factura-registro.js') }}"></script>
@endpush
