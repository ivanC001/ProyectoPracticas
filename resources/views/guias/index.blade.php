@extends('admin.main')

@section('contenido')
@php
    $motivos = config('sunat_guia.motivos_traslado', []);
    $documentosRelacionados = config('sunat_guia.documentos_relacionados', []);
    $empresa = config('empresa', []);
    $empresaGuiaDefaults = [
        'ubigeo' => (string) data_get($empresa, 'ubigeo', ''),
        'direccion' => (string) data_get($empresa, 'direccion', ''),
        'ruc' => (string) data_get($empresa, 'ruc', ''),
    ];
@endphp
<div class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">
                        <i class="fas fa-truck-loading text-primary"></i> Guias de Remision
                    </h4>
                    <small class="text-muted">Remitente (09) y Transportista (31)</small>
                </div>
                <button class="btn btn-primary" data-toggle="modal" data-target="#modalGuiaRemision" onclick="prepararNuevaGuia()">
                    <i class="fas fa-plus-circle"></i> Nueva Guia
                </button>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="guiaBuscador" class="form-control" placeholder="Buscar por guia, destinatario, documento o placa...">
                    </div>
                    <div class="col-md-3">
                        <select id="guiaFiltroTipo" class="form-control">
                            <option value="">Todos los tipos</option>
                            <option value="09">09 - Remitente</option>
                            <option value="31">31 - Transportista</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th width="220">Acciones</th>
                                <th>#</th>
                                <th>Guia</th>
                                <th>Tipo</th>
                                <th>Destinatario</th>
                                <th>Traslado</th>
                                <th>Modalidad</th>
                                <th>Peso</th>
                                <th>Emitido por</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="guiaTableBody">
                            <tr>
                                <td colspan="10" class="text-center">
                                    <div class="spinner-border text-primary"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="guiaPaginacion" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGuiaRemision" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModalGuia">Nueva Guia de Remision</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                @include('guias.registro')
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" onclick="guardarGuiaRemision()">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const GUIA_MOTIVOS = @json($motivos);
const GUIA_DOCUMENTOS_REL = @json($documentosRelacionados);
const EMPRESA_GUIA_DEFAULTS = @json($empresaGuiaDefaults);
let guiaSearch = '';
let guiaTipo = '';
let guiaDebounce = null;
let facturaRelacionadaDebounce = null;
let clienteDestinatarioDebounce = null;
let remitenteRelacionadaDebounce = null;

function toLocalDateTimeInput(date) {
    const offset = date.getTimezoneOffset();
    return new Date(date.getTime() - offset * 60000).toISOString().slice(0, 16);
}

function setFechasGuia() {
    const now = new Date();
    $('#gr_fecha_emision').val(toLocalDateTimeInput(now));
    $('#gr_fecha_traslado').val(toLocalDateTimeInput(now).slice(0, 10));
}

function getGuiaPdfUrl(id) {
    return `/guias-remision/pdf/${id}?_t=${Date.now()}`;
}

function getGuiaPdfDownloadUrl(id) {
    return `/guias-remision/pdf/${id}/descargar?_t=${Date.now()}`;
}

function getGuiaXmlUrl(id) {
    return `/guias-remision/xml/${id}?_t=${Date.now()}`;
}

function getGuiaXmlDownloadUrl(id) {
    return `/guias-remision/xml/${id}/descargar?_t=${Date.now()}`;
}

function getBadgeEstado(estado) {
    if (estado === 'aceptado') return '<span class="badge badge-success">Aceptado</span>';
    if (estado === 'rechazado') return '<span class="badge badge-danger">Rechazado</span>';
    if (estado === 'procesando') return '<span class="badge badge-info">Procesando</span>';
    if (estado === 'error') return '<span class="badge badge-dark">Error</span>';
    return '<span class="badge badge-warning">Pendiente</span>';
}

function formatDateOnly(value) {
    const raw = String(value || '');
    if (raw.length >= 10) {
        const yyyy = raw.slice(0, 4);
        const mm = raw.slice(5, 7);
        const dd = raw.slice(8, 10);
        if (yyyy && mm && dd) return `${dd}/${mm}/${yyyy}`;
    }
    return '-';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function syncMotivoDescripcion() {
    const code = $('#gr_motivo_codigo').val();
    const found = GUIA_MOTIVOS.find(m => m.codigo === code);
    if (found) $('#gr_motivo_descripcion').val(found.descripcion);
}

function toggleBloquesTransporte() {
    const modalidad = $('#gr_modalidad').val();
    const tipoGuia = $('#gr_tipo_documento').val();

    const showTransportista = modalidad === '01' || tipoGuia === '31';
    const showConductorVehiculo = modalidad === '01' || modalidad === '02' || tipoGuia === '31';
    const isPublico = modalidad === '01' || tipoGuia === '31';
    const isPrivado = modalidad === '02' && tipoGuia !== '31';

    $('#bloqueTransportista').toggle(showTransportista);
    $('#bloquePrivado').toggle(showConductorVehiculo);

    $('#gr_cond_nombres').closest('.col-md-4').toggle(isPrivado);
    $('#gr_cond_licencia_wrap').toggle(isPrivado);
    $('#gr_veh_sec_wrap').toggle(isPrivado);

    $('#gr_conductor_titulo').text(isPublico ? 'Unidad y conductor del traslado' : 'Conductor y vehiculo');
    $('#gr_conductor_help').text(
        isPublico
            ? 'SUNAT te pedira al menos documento del conductor y placa principal de la unidad.'
            : 'Completa conductor, licencia y placas del traslado privado.'
    );
    $('#gr_transporte_help').text(
        isPublico
            ? 'Publico: transportista con RUC, MTC, placa y documento del conductor.'
            : 'Privado: conductor + licencia + placa principal; la placa secundaria es opcional.'
    );
}

function ocultarResultadosFactura() {
    $('#gr_factura_results').addClass('d-none').empty();
}

function limpiarFacturaRelacionada() {
    $('#gr_venta_id').val('');
    $('#gr_factura_search').val('');
    $('#gr_factura_selected').text('Sin comprobante relacionado');
    ocultarResultadosFactura();
}

function ocultarResultadosRemitente() {
    $('#gr_remitente_results').addClass('d-none').empty();
}

function limpiarRemitenteRelacionado() {
    $('#gr_guia_remitente_id').val('');
    $('#gr_remitente_search').val('');
    $('#gr_remitente_selected').text('Sin guia remitente relacionada');
    ocultarResultadosRemitente();
}

function cargarDocumentoRelacionadoDesdeSeleccion(tipo, numero, emisor = '') {
    const tipoValue = String(tipo || '').trim();
    if (!tipoValue || !String(numero || '').trim()) return;

    $('#gr_doc_rel_tipo').val(tipoValue);
    $('#gr_doc_rel_numero').val(String(numero || '').trim());
    if (String(emisor || '').trim()) {
        $('#gr_doc_rel_emisor').val(String(emisor || '').trim());
    } else if (!String($('#gr_doc_rel_emisor').val() || '').trim()) {
        $('#gr_doc_rel_emisor').val(EMPRESA_GUIA_DEFAULTS.ruc || '');
    }
}

function setPartidaEmpresa() {
    $('#gr_partida_ubigeo').val(EMPRESA_GUIA_DEFAULTS.ubigeo || '');
    $('#gr_partida_direccion').val(EMPRESA_GUIA_DEFAULTS.direccion || '');
}

function ocultarResultadosCliente() {
    $('#gr_cliente_results').addClass('d-none').empty();
}

function limpiarClienteSeleccionado() {
    $('#gr_cliente_selected').text('Sin cliente seleccionado');
}

function renderResultadosClientes(items) {
    const box = $('#gr_cliente_results');
    box.empty();

    if (!items.length) {
        box.append('<div class="list-group-item text-muted small">Sin clientes</div>');
        box.removeClass('d-none');
        return;
    }

    items.forEach(c => {
        box.append(`
            <button type="button" class="list-group-item list-group-item-action py-2" onclick="seleccionarClienteDestinatario(${Number(c.id)})">
                <div><strong>${escapeHtml(c.razon_social || '')}</strong></div>
                <small class="text-muted">${escapeHtml(c.num_doc || '')}</small>
            </button>
        `);
    });

    box.removeClass('d-none');
}

async function buscarClientesDestinatario(search) {
    const term = String(search || '').trim();

    try {
        const resp = await apiFetch(`/api/guias-remision/clientes?search=${encodeURIComponent(term)}`);
        renderResultadosClientes(resp.data || []);
    } catch (err) {
        ocultarResultadosCliente();
    }
}

async function seleccionarClienteDestinatario(id) {
    try {
        const resp = await apiFetch(`/api/guias-remision/clientes/${id}`);
        const c = resp.data;

        $('#gr_dest_tipo_doc').val(String(c.tipo_doc || '6'));
        $('#gr_dest_num_doc').val(c.num_doc || '');
        $('#gr_dest_razon_social').val(c.razon_social || '');
        $('#gr_cliente_search').val(`${c.razon_social || ''} - ${c.num_doc || ''}`);
        $('#gr_cliente_selected').text(`Cliente seleccionado: ${c.razon_social || ''}`);
        if (!String($('#gr_llegada_direccion').val() || '').trim() && String(c.direccion || '').trim()) {
            $('#gr_llegada_direccion').val(c.direccion || '');
        }
        ocultarResultadosCliente();
    } catch (err) {
        Swal.fire('Error', err.message || 'No se pudo cargar el cliente', 'error');
    }
}

async function registrarNuevoClienteDesdeGuia() {
    const tipoDoc = String($('#gr_dest_tipo_doc').val() || '').trim();
    const numDoc = String($('#gr_dest_num_doc').val() || '').trim();
    const razon = String($('#gr_dest_razon_social').val() || '').trim();

    if (!['1', '6'].includes(tipoDoc)) {
        Swal.fire('Atencion', 'Para registrar cliente nuevo desde guia, usa DNI o RUC.', 'warning');
        return;
    }

    if (!numDoc || !razon) {
        Swal.fire('Atencion', 'Completa tipo doc, numero y razon social del destinatario.', 'warning');
        return;
    }

    const direccion = String($('#gr_llegada_direccion').val() || '').trim();

    try {
        const resp = await apiFetch('/api/guias-remision/clientes', {
            method: 'POST',
            body: JSON.stringify({
                tipo_doc: tipoDoc,
                num_doc: numDoc,
                razon_social: razon,
                direccion: direccion || null,
            }),
        });

        $('#gr_cliente_selected').text(`Cliente registrado: ${resp.data?.razon_social || razon}`);
        Swal.fire('OK', resp.message || 'Cliente registrado correctamente', 'success');
    } catch (err) {
        if (err?.errors?.num_doc?.[0]?.toLowerCase?.().includes('registrado')) {
            $('#gr_cliente_selected').text('Cliente ya existente, usa el buscador para seleccionarlo.');
        }
        Swal.fire('Error', err.message || 'No se pudo registrar el cliente', 'error');
    }
}

function renderResultadosFacturas(items) {
    const box = $('#gr_factura_results');
    box.empty();

    if (!items.length) {
        box.append('<div class="list-group-item text-muted small">Sin resultados</div>');
        box.removeClass('d-none');
        return;
    }

    items.forEach(f => {
        const total = `${f.moneda === 'USD' ? 'US$' : 'S/'} ${Number(f.total_venta || 0).toFixed(2)}`;
        box.append(`
            <button type="button" class="list-group-item list-group-item-action py-2" onclick="seleccionarFacturaRelacionada(${Number(f.id)})">
                <div><strong>${escapeHtml(f.numero_comprobante)}</strong> - ${escapeHtml(f.nombre_cliente || '')}</div>
                <small class="text-muted">${escapeHtml(f.numero_documento_cliente || '')} | ${total}</small>
            </button>
        `);
    });

    box.removeClass('d-none');
}

async function buscarFacturasRelacionadas(search) {
    const query = String(search || '').trim();

    try {
        const resp = await apiFetch(`/api/guias-remision/facturas?search=${encodeURIComponent(query)}`);
        renderResultadosFacturas(resp.data || []);
    } catch (err) {
        ocultarResultadosFactura();
    }
}

async function seleccionarFacturaRelacionada(id) {
    try {
        const resp = await apiFetch(`/api/guias-remision/facturas/${id}`);
        const venta = resp.data;
        const tipoComprobante = String(venta.tipo_documento || '') === '03' ? 'Boleta' : 'Factura';

        $('#gr_venta_id').val(venta.id);
        $('#gr_factura_search').val(`${venta.numero_comprobante} - ${venta.nombre_cliente || ''}`);
        $('#gr_factura_selected').text(`${tipoComprobante} relacionada: ${venta.numero_comprobante}`);
        limpiarRemitenteRelacionado();
        ocultarResultadosFactura();
        cargarDocumentoRelacionadoDesdeSeleccion(
            venta.tipo_documento || '',
            venta.numero_comprobante || '',
            EMPRESA_GUIA_DEFAULTS.ruc || ''
        );

        // Autocompletar destinatario
        $('#gr_dest_tipo_doc').val(venta.tipo_documento_cliente || '6');
        $('#gr_dest_num_doc').val(venta.numero_documento_cliente || '');
        $('#gr_dest_razon_social').val(venta.nombre_cliente || '');
        $('#gr_cliente_search').val(`${venta.nombre_cliente || ''} - ${venta.numero_documento_cliente || ''}`);
        $('#gr_cliente_selected').text(`Cliente desde factura: ${venta.nombre_cliente || ''}`);

        // Cargar detalle desde la factura relacionada
        $('#gr_detalles_body').empty();
        (venta.detalles || []).forEach(d => {
            agregarFilaDetalleGuia({
                tipo_item: d.tipo_item || null,
                codigo: d.codigo_producto || null,
                descripcion: d.descripcion || '',
                unidad: d.unidad || 'NIU',
                cantidad: Number(d.cantidad || 0) || 1,
            });
        });

        if (!venta.detalles || !venta.detalles.length) {
            agregarFilaDetalleGuia();
        }
    } catch (err) {
        Swal.fire('Error', err.message || 'No se pudo cargar la factura relacionada', 'error');
    }
}

function renderResultadosRemitentes(items) {
    const box = $('#gr_remitente_results');
    box.empty();

    if (!items.length) {
        box.append('<div class="list-group-item text-muted small">Sin resultados</div>');
        box.removeClass('d-none');
        return;
    }

    items.forEach(g => {
        box.append(`
            <button type="button" class="list-group-item list-group-item-action py-2" onclick="seleccionarRemitenteRelacionado(${Number(g.id)})">
                <div><strong>${escapeHtml(g.numero_guia || '')}</strong> - ${escapeHtml(g.destinatario_razon_social || '')}</div>
                <small class="text-muted">${escapeHtml(g.destinatario_num_doc || '')}</small>
            </button>
        `);
    });

    box.removeClass('d-none');
}

async function buscarRemitentesRelacionados(search) {
    const query = String(search || '').trim();

    try {
        const resp = await apiFetch(`/api/guias-remision/remitentes?search=${encodeURIComponent(query)}`);
        renderResultadosRemitentes(resp.data || []);
    } catch (err) {
        ocultarResultadosRemitente();
    }
}

async function seleccionarRemitenteRelacionado(id) {
    try {
        const resp = await apiFetch(`/api/guias-remision/remitentes/${id}`);
        const guiaRemitente = resp.data;

        $('#gr_guia_remitente_id').val(guiaRemitente.id);
        $('#gr_remitente_search').val(`${guiaRemitente.numero_guia} - ${guiaRemitente.destinatario_razon_social || ''}`);
        $('#gr_remitente_selected').text(`Guia remitente relacionada: ${guiaRemitente.numero_guia}`);
        limpiarFacturaRelacionada();
        ocultarResultadosRemitente();
        cargarDocumentoRelacionadoDesdeSeleccion(
            '09',
            guiaRemitente.numero_guia || '',
            EMPRESA_GUIA_DEFAULTS.ruc || ''
        );

        $('#gr_dest_tipo_doc').val('6');
        $('#gr_dest_num_doc').val(guiaRemitente.destinatario_num_doc || '');
        $('#gr_dest_razon_social').val(guiaRemitente.destinatario_razon_social || '');
        $('#gr_cliente_search').val(`${guiaRemitente.destinatario_razon_social || ''} - ${guiaRemitente.destinatario_num_doc || ''}`);
        $('#gr_cliente_selected').text(`Cliente desde guia remitente: ${guiaRemitente.destinatario_razon_social || ''}`);

        if (!String($('#gr_llegada_ubigeo').val() || '').trim() && String(guiaRemitente.llegada_ubigeo || '').trim()) {
            $('#gr_llegada_ubigeo').val(guiaRemitente.llegada_ubigeo || '');
        }
        if (!String($('#gr_llegada_direccion').val() || '').trim() && String(guiaRemitente.llegada_direccion || '').trim()) {
            $('#gr_llegada_direccion').val(guiaRemitente.llegada_direccion || '');
        }

        $('#gr_detalles_body').empty();
        (guiaRemitente.detalles || []).forEach(d => {
            agregarFilaDetalleGuia({
                tipo_item: d.tipo_item || null,
                codigo: d.codigo || null,
                descripcion: d.descripcion || '',
                unidad: d.unidad || 'NIU',
                cantidad: Number(d.cantidad || 0) || 1,
            });
        });

        if (!guiaRemitente.detalles || !guiaRemitente.detalles.length) {
            agregarFilaDetalleGuia();
        }
    } catch (err) {
        Swal.fire('Error', err.message || 'No se pudo cargar la guia remitente', 'error');
    }
}

function clearGuiaErrors() {
    $('#guiaValidationBox').addClass('d-none').html('');
    $('#formGuiaRemision .is-invalid').removeClass('is-invalid');
}

function showGuiaErrors(err) {
    clearGuiaErrors();
    if (!err || !err.errors) {
        Swal.fire('Error', err?.message || 'No se pudo procesar la guia', 'error');
        return;
    }

    const map = {
        'tipo_documento': 'gr_tipo_documento',
        'fecha_emision': 'gr_fecha_emision',
        'fecha_traslado': 'gr_fecha_traslado',
        'motivo_traslado_codigo': 'gr_motivo_codigo',
        'motivo_traslado_descripcion': 'gr_motivo_descripcion',
        'modalidad_transporte': 'gr_modalidad',
        'peso_total': 'gr_peso_total',
        'destinatario.tipo_doc': 'gr_dest_tipo_doc',
        'destinatario.num_doc': 'gr_dest_num_doc',
        'destinatario.razon_social': 'gr_dest_razon_social',
        'partida.ubigeo': 'gr_partida_ubigeo',
        'partida.direccion': 'gr_partida_direccion',
        'llegada.ubigeo': 'gr_llegada_ubigeo',
        'llegada.direccion': 'gr_llegada_direccion',
        'transportista.num_doc': 'gr_trans_num_doc',
        'transportista.razon_social': 'gr_trans_razon_social',
        'transportista.reg_mtc': 'gr_trans_reg_mtc',
        'conductor.num_doc': 'gr_cond_num_doc',
        'conductor.nombres': 'gr_cond_nombres',
        'conductor.licencia': 'gr_cond_licencia',
        'vehiculo.placa': 'gr_veh_placa',
        'venta_id': 'gr_factura_search',
        'guia_remitente_id': 'gr_remitente_search',
        'documento_relacionado.tipo': 'gr_doc_rel_tipo',
        'documento_relacionado.numero': 'gr_doc_rel_numero',
        'documento_relacionado.emisor': 'gr_doc_rel_emisor',
        'detalles': 'gr_detalles_body',
    };

    const messages = [];
    Object.keys(err.errors).forEach(key => {
        (err.errors[key] || []).forEach(msg => messages.push(msg));
        const field = map[key];
        if (field && document.getElementById(field)) {
            document.getElementById(field).classList.add('is-invalid');
        }
    });

    $('#guiaValidationBox')
        .removeClass('d-none')
        .html(`<strong>Corrige lo siguiente:</strong><ul class="mb-0 mt-2">${messages.map(m => `<li>${m}</li>`).join('')}</ul>`);
}

function agregarFilaDetalleGuia(item = {}) {
    const row = `
        <tr>
            <td>
                <select class="form-control form-control-sm gr-det-tipo">
                    <option value="">-</option>
                    <option value="producto" ${item.tipo_item === 'producto' ? 'selected' : ''}>Producto</option>
                    <option value="servicio" ${item.tipo_item === 'servicio' ? 'selected' : ''}>Servicio</option>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm gr-det-codigo" value="${item.codigo || ''}"></td>
            <td><input type="text" class="form-control form-control-sm gr-det-descripcion" value="${item.descripcion || ''}"></td>
            <td><input type="text" class="form-control form-control-sm gr-det-unidad" value="${item.unidad || 'NIU'}"></td>
            <td><input type="number" min="0.001" step="0.001" class="form-control form-control-sm gr-det-cantidad" value="${item.cantidad || 1}"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('tr').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>
    `;

    $('#gr_detalles_body').append(row);
}

function readDetallesGuia() {
    const out = [];
    $('#gr_detalles_body tr').each(function () {
        const row = $(this);
        const descripcion = String(row.find('.gr-det-descripcion').val() || '').trim();
        if (!descripcion) return;
        out.push({
            tipo_item: String(row.find('.gr-det-tipo').val() || '').trim() || null,
            codigo: String(row.find('.gr-det-codigo').val() || '').trim() || null,
            descripcion,
            unidad: String(row.find('.gr-det-unidad').val() || 'NIU').trim() || 'NIU',
            cantidad: Number(row.find('.gr-det-cantidad').val() || 0),
        });
    });
    return out;
}

function construirPayloadGuia() {
    const docRelTipo = String($('#gr_doc_rel_tipo').val() || '').trim();
    const docRelNumero = String($('#gr_doc_rel_numero').val() || '').trim();
    const docRelEmisor = String($('#gr_doc_rel_emisor').val() || '').trim();
    const payloadDocumentoRelacionado = (docRelTipo || docRelNumero || docRelEmisor)
        ? {
            tipo: docRelTipo || null,
            numero: docRelNumero || null,
            emisor: docRelEmisor || null,
        }
        : null;

    return {
        tipo_documento: $('#gr_tipo_documento').val(),
        fecha_emision: ($('#gr_fecha_emision').val() || '').replace('T', ' ') + ':00',
        fecha_traslado: $('#gr_fecha_traslado').val(),
        motivo_traslado_codigo: $('#gr_motivo_codigo').val(),
        motivo_traslado_descripcion: $('#gr_motivo_descripcion').val(),
        modalidad_transporte: $('#gr_modalidad').val(),
        peso_total: Number($('#gr_peso_total').val() || 0),
        unidad_peso: $('#gr_unidad_peso').val() || 'KGM',
        numero_bultos: $('#gr_numero_bultos').val() ? Number($('#gr_numero_bultos').val()) : null,
        observacion: ($('#gr_observacion').val() || '').trim() || null,
        venta_id: $('#gr_venta_id').val() ? Number($('#gr_venta_id').val()) : null,
        guia_remitente_id: $('#gr_guia_remitente_id').val() ? Number($('#gr_guia_remitente_id').val()) : null,
        documento_relacionado: payloadDocumentoRelacionado,
        destinatario: {
            tipo_doc: $('#gr_dest_tipo_doc').val(),
            num_doc: $('#gr_dest_num_doc').val(),
            razon_social: $('#gr_dest_razon_social').val(),
        },
        partida: {
            ubigeo: ($('#gr_partida_ubigeo').val() || '').trim() || null,
            direccion: $('#gr_partida_direccion').val(),
        },
        llegada: {
            ubigeo: ($('#gr_llegada_ubigeo').val() || '').trim() || null,
            direccion: $('#gr_llegada_direccion').val(),
        },
        transportista: {
            tipo_doc: $('#gr_trans_tipo_doc').val(),
            num_doc: $('#gr_trans_num_doc').val(),
            razon_social: $('#gr_trans_razon_social').val(),
            reg_mtc: $('#gr_trans_reg_mtc').val(),
        },
        conductor: {
            tipo_doc: $('#gr_cond_tipo_doc').val(),
            num_doc: $('#gr_cond_num_doc').val(),
            nombres: $('#gr_cond_nombres').val(),
            licencia: $('#gr_cond_licencia').val(),
        },
        vehiculo: {
            placa: $('#gr_veh_placa').val(),
            secundario_placa: $('#gr_veh_sec_placa').val(),
        },
        detalles: readDetallesGuia(),
    };
}

function prepararNuevaGuia() {
    clearGuiaErrors();
    $('#tituloModalGuia').text('Nueva Guia de Remision');
    $('#guia_id').val('');
    $('#formGuiaRemision')[0].reset();
    limpiarFacturaRelacionada();
    limpiarRemitenteRelacionado();
    $('#gr_cliente_search').val('');
    limpiarClienteSeleccionado();
    setFechasGuia();
    setPartidaEmpresa();
    $('#gr_doc_rel_tipo').val('');
    $('#gr_doc_rel_numero').val('');
    $('#gr_doc_rel_emisor').val(EMPRESA_GUIA_DEFAULTS.ruc || '');
    $('#gr_unidad_peso').val('KGM');
    $('#gr_peso_total').val('1.000');
    $('#gr_detalles_body').empty();
    agregarFilaDetalleGuia();
    syncMotivoDescripcion();
    toggleBloquesTransporte();
}

async function guardarGuiaRemision() {
    clearGuiaErrors();
    const id = Number($('#guia_id').val() || 0);
    const payload = construirPayloadGuia();

    const url = id > 0 ? `/api/guias-remision/${id}` : '/api/guias-remision';
    const method = id > 0 ? 'PUT' : 'POST';

    try {
        await apiFetch(url, {
            method,
            body: JSON.stringify(payload),
        });

        Swal.fire('OK', id > 0 ? 'Guia actualizada' : 'Guia registrada', 'success');
        $('#modalGuiaRemision').modal('hide');
        cargarGuias();
    } catch (err) {
        showGuiaErrors(err);
    }
}

async function cargarGuias(page = 1) {
    const params = new URLSearchParams({
        page: String(page),
        search: guiaSearch,
    });

    if (guiaTipo) params.append('tipo_documento', guiaTipo);

    try {
        const resp = await apiFetch(`/api/guias-remision?${params.toString()}`);
        const tbody = $('#guiaTableBody');
        tbody.empty();

        if (!resp.data || !resp.data.length) {
            tbody.html('<tr><td colspan="10" class="text-center text-muted">No hay guias registradas</td></tr>');
            $('#guiaPaginacion').html('');
            return;
        }

        resp.data.forEach(g => {
            const modalidad = g.modalidad_transporte === '01' ? 'Publico' : 'Privado';
            const tipo = g.tipo_documento === '31' ? '31 - Transportista' : '09 - Remitente';
            const canPdf = g.estado_envio === 'aceptado';
            const canXml = !!g.archivo_xml;
            const reason = (g.mensaje_error || g.descripcion_respuesta_sunat || '').trim();
            const docRel = String(g.documento_rel_numero || '').trim();
                tbody.append(`
                <tr>
                    <td class="text-center">
                        <button class="btn btn-sm btn-info" onclick="verGuia(${g.id})" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-info ${canPdf ? '' : 'disabled'}" ${canPdf ? `onclick="window.open('${getGuiaPdfUrl(g.id)}','_blank')"` : ''} title="Ver PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary ${canPdf ? '' : 'disabled'}" ${canPdf ? `onclick="window.open('${getGuiaPdfDownloadUrl(g.id)}','_blank')"` : ''} title="Descargar PDF">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="btn btn-sm btn-dark ${canXml ? '' : 'disabled'}" ${canXml ? `onclick="window.open('${getGuiaXmlUrl(g.id)}','_blank')"` : ''} title="Ver XML">
                            <i class="fas fa-code"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-dark ${canXml ? '' : 'disabled'}" ${canXml ? `onclick="window.open('${getGuiaXmlDownloadUrl(g.id)}','_blank')"` : ''} title="Descargar XML">
                            <i class="fas fa-file-download"></i>
                        </button>
                    </td>
                    <td>${g.id}</td>
                    <td><strong>${g.numero_guia}</strong>${docRel ? `<br><small class="text-muted">Doc rel: ${escapeHtml(docRel)}</small>` : ''}</td>
                    <td>${tipo}</td>
                    <td>${g.destinatario_razon_social}<br><small>${g.destinatario_num_doc}</small></td>
                    <td>${formatDateOnly(g.fecha_traslado)}</td>
                    <td>${modalidad}</td>
                    <td>${Number(g.peso_total || 0).toFixed(3)} ${g.unidad_peso || 'KGM'}</td>
                    <td>${escapeHtml(g.emisor?.name || 'Sin usuario')}</td>
                    <td>
                        ${getBadgeEstado(g.estado_envio)}
                        ${reason ? `<br><small class="text-danger">${escapeHtml(reason)}</small>` : ''}
                    </td>
                </tr>
            `);
        });

        renderPaginacionGuias(resp.pagination);
    } catch (err) {
        $('#guiaTableBody').html('<tr><td colspan="10" class="text-center text-danger">No se pudo cargar guias</td></tr>');
    }
}

function renderPaginacionGuias(p) {
    if (!p || p.last_page <= 1) {
        $('#guiaPaginacion').html('');
        return;
    }

    let html = '';
    for (let i = 1; i <= p.last_page; i++) {
        html += `<button class="btn btn-sm ${i === p.current_page ? 'btn-primary' : 'btn-light'} mr-1" onclick="cargarGuias(${i})">${i}</button>`;
    }
    $('#guiaPaginacion').html(html);
}

async function editarGuia(id) {
    try {
        const resp = await apiFetch(`/api/guias-remision/${id}`);
        const g = resp.data;

        clearGuiaErrors();
        $('#tituloModalGuia').text(`Editar Guia ${g.numero_guia}`);
        $('#guia_id').val(g.id);
        $('#gr_tipo_documento').val(g.tipo_documento);
        $('#gr_fecha_emision').val(String(g.fecha_emision || '').slice(0, 16).replace(' ', 'T'));
        $('#gr_fecha_traslado').val(String(g.fecha_traslado || '').slice(0, 10));
        $('#gr_venta_id').val(g.venta_id || '');
        $('#gr_guia_remitente_id').val(g.guia_remitente_id || '');
        limpiarFacturaRelacionada();
        limpiarRemitenteRelacionado();

        if (g.venta_id && g.venta) {
            const tipoComprobante = String(g.venta.tipo_documento || '') === '03' ? 'Boleta' : 'Factura';
            $('#gr_venta_id').val(g.venta.id || g.venta_id);
            $('#gr_factura_search').val(`${g.venta.numero_comprobante} - ${g.venta.nombre_cliente || ''}`);
            $('#gr_factura_selected').text(`${tipoComprobante} relacionada: ${g.venta.numero_comprobante}`);
        } else if (g.venta_id) {
            $('#gr_venta_id').val(g.venta_id);
            $('#gr_factura_search').val(`Comprobante #${g.venta_id}`);
            $('#gr_factura_selected').text(`Comprobante relacionado: #${g.venta_id}`);
        }

        if (g.guia_remitente_id && g.guia_remitente) {
            $('#gr_guia_remitente_id').val(g.guia_remitente.id || g.guia_remitente_id);
            $('#gr_remitente_search').val(`${g.guia_remitente.numero_guia} - ${g.guia_remitente.destinatario_razon_social || ''}`);
            $('#gr_remitente_selected').text(`Guia remitente relacionada: ${g.guia_remitente.numero_guia}`);
        } else if (g.guia_remitente_id) {
            $('#gr_guia_remitente_id').val(g.guia_remitente_id);
            $('#gr_remitente_search').val(`Guia #${g.guia_remitente_id}`);
            $('#gr_remitente_selected').text(`Guia remitente relacionada: #${g.guia_remitente_id}`);
        }

        $('#gr_doc_rel_tipo').val(g.documento_rel_tipo || '');
        $('#gr_doc_rel_numero').val(g.documento_rel_numero || '');
        $('#gr_doc_rel_emisor').val(g.documento_rel_emisor || EMPRESA_GUIA_DEFAULTS.ruc || '');

        $('#gr_motivo_codigo').val(g.motivo_traslado_codigo);
        $('#gr_motivo_descripcion').val(g.motivo_traslado_descripcion || '');
        $('#gr_modalidad').val(g.modalidad_transporte);
        $('#gr_peso_total').val(Number(g.peso_total || 0).toFixed(3));
        $('#gr_unidad_peso').val(g.unidad_peso || 'KGM');
        $('#gr_numero_bultos').val(g.numero_bultos || '');
        $('#gr_observacion').val(g.observacion || '');

        $('#gr_dest_tipo_doc').val(g.destinatario_tipo_doc || '6');
        $('#gr_dest_num_doc').val(g.destinatario_num_doc || '');
        $('#gr_dest_razon_social').val(g.destinatario_razon_social || '');
        $('#gr_cliente_search').val(`${g.destinatario_razon_social || ''} - ${g.destinatario_num_doc || ''}`);
        $('#gr_cliente_selected').text(g.destinatario_razon_social ? `Cliente en guia: ${g.destinatario_razon_social}` : 'Sin cliente seleccionado');

        $('#gr_partida_ubigeo').val(g.partida_ubigeo || '');
        $('#gr_partida_direccion').val(g.partida_direccion || '');
        $('#gr_llegada_ubigeo').val(g.llegada_ubigeo || '');
        $('#gr_llegada_direccion').val(g.llegada_direccion || '');
        if (!$('#gr_partida_ubigeo').val() || !$('#gr_partida_direccion').val()) {
            setPartidaEmpresa();
        }

        $('#gr_trans_tipo_doc').val(g.transportista_tipo_doc || '6');
        $('#gr_trans_num_doc').val(g.transportista_num_doc || '');
        $('#gr_trans_razon_social').val(g.transportista_razon_social || '');
        $('#gr_trans_reg_mtc').val(g.transportista_reg_mtc || '');

        $('#gr_cond_tipo_doc').val(g.conductor_tipo_doc || '1');
        $('#gr_cond_num_doc').val(g.conductor_num_doc || '');
        $('#gr_cond_nombres').val(g.conductor_nombres || '');
        $('#gr_cond_licencia').val(g.conductor_licencia || '');
        $('#gr_veh_placa').val(g.vehiculo_placa || '');
        $('#gr_veh_sec_placa').val(g.vehiculo_secundario_placa || '');

        $('#gr_detalles_body').empty();
        (g.detalles || []).forEach(d => agregarFilaDetalleGuia(d));
        if (!g.detalles || !g.detalles.length) agregarFilaDetalleGuia();

        toggleBloquesTransporte();
        $('#modalGuiaRemision').modal('show');
    } catch (err) {
        Swal.fire('Error', err.message || 'No se pudo cargar la guia', 'error');
    }
}

async function verGuia(id) {
    try {
        const resp = await apiFetch(`/api/guias-remision/${id}`);
        const g = resp.data;
        const list = (g.detalles || [])
            .map(d => `<li>${Number(d.cantidad || 0).toFixed(3)} ${d.unidad || 'NIU'} - ${d.descripcion}</li>`)
            .join('');

        Swal.fire({
            title: `Guia ${g.numero_guia}`,
            html: `
                <div class="text-left">
                    <p class="mb-1"><strong>Tipo:</strong> ${g.tipo_documento === '31' ? 'Transportista' : 'Remitente'}</p>
                    <p class="mb-1"><strong>Destinatario:</strong> ${g.destinatario_razon_social}</p>
                    <p class="mb-1"><strong>Ruta:</strong> ${g.partida_direccion} -> ${g.llegada_direccion}</p>
                    <p class="mb-1"><strong>Fecha traslado:</strong> ${formatDateOnly(g.fecha_traslado)}</p>
                    <p class="mb-1"><strong>Documento relacionado:</strong> ${escapeHtml(g.documento_rel_tipo || '-')}${g.documento_rel_numero ? ` - ${escapeHtml(g.documento_rel_numero)}` : ''}</p>
                    <p class="mb-2"><strong>Estado:</strong> ${g.estado_envio}</p>
                    <strong>Items:</strong>
                    <ul class="mt-1">${list || '<li>Sin items</li>'}</ul>
                </div>
            `,
            width: 700,
            confirmButtonText: 'Cerrar',
        });
    } catch (err) {
        Swal.fire('Error', err.message || 'No se pudo ver la guia', 'error');
    }
}

function eliminarGuia(id) {
    Swal.fire({
        title: 'Eliminar guia?',
        text: 'Esta accion no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar',
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        try {
            await apiFetch(`/api/guias-remision/${id}`, { method: 'DELETE' });
            Swal.fire('OK', 'Guia eliminada', 'success');
            cargarGuias();
        } catch (err) {
            Swal.fire('Error', err.message || 'No se pudo eliminar', 'error');
        }
    });
}

$(document).ready(function () {
    prepararNuevaGuia();
    cargarGuias();

    $('#guiaBuscador').on('input', function () {
        clearTimeout(guiaDebounce);
        guiaDebounce = setTimeout(() => {
            guiaSearch = $(this).val().trim();
            cargarGuias(1);
        }, 300);
    });

    $('#guiaFiltroTipo').on('change', function () {
        guiaTipo = $(this).val();
        cargarGuias(1);
    });

    $('#gr_motivo_codigo').on('change', syncMotivoDescripcion);
    $('#gr_modalidad, #gr_tipo_documento').on('change', toggleBloquesTransporte);
    $('#gr_factura_search').on('input', function () {
        $('#gr_venta_id').val('');
        $('#gr_factura_selected').text('Sin comprobante relacionado');
        const term = $(this).val();
        if (term.trim() !== '') {
            limpiarRemitenteRelacionado();
        }
        clearTimeout(facturaRelacionadaDebounce);
        facturaRelacionadaDebounce = setTimeout(() => buscarFacturasRelacionadas(term), 250);
    });

    $('#gr_factura_search').on('focus', function () {
        buscarFacturasRelacionadas($(this).val());
    });
    $('#gr_remitente_search').on('input', function () {
        $('#gr_guia_remitente_id').val('');
        $('#gr_remitente_selected').text('Sin guia remitente relacionada');
        const term = $(this).val();
        if (term.trim() !== '') {
            limpiarFacturaRelacionada();
        }
        clearTimeout(remitenteRelacionadaDebounce);
        remitenteRelacionadaDebounce = setTimeout(() => buscarRemitentesRelacionados(term), 250);
    });

    $('#gr_remitente_search').on('focus', function () {
        buscarRemitentesRelacionados($(this).val());
    });
    $('#gr_cliente_search').on('input', function () {
        clearTimeout(clienteDestinatarioDebounce);
        const term = $(this).val();
        clienteDestinatarioDebounce = setTimeout(() => buscarClientesDestinatario(term), 250);
    });
    $('#gr_cliente_search').on('focus', function () {
        buscarClientesDestinatario($(this).val());
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('#gr_factura_search, #gr_factura_results').length) {
            ocultarResultadosFactura();
        }
        if (!$(event.target).closest('#gr_cliente_search, #gr_cliente_results').length) {
            ocultarResultadosCliente();
        }
        if (!$(event.target).closest('#gr_remitente_search, #gr_remitente_results').length) {
            ocultarResultadosRemitente();
        }
    });

    $('#modalGuiaRemision').on('hidden.bs.modal', function () {
        prepararNuevaGuia();
    });
});
</script>
@endpush
