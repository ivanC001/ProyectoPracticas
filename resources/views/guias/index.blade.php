@extends('admin.main')

@section('contenido')
@php
    $motivos = config('sunat_guia.motivos_traslado', []);
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
                                <th width="250">Acciones</th>
                                <th>#</th>
                                <th>Guia</th>
                                <th>Tipo</th>
                                <th>Destinatario</th>
                                <th>Traslado</th>
                                <th>Modalidad</th>
                                <th>Peso</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="guiaTableBody">
                            <tr>
                                <td colspan="9" class="text-center">
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
let guiaSearch = '';
let guiaTipo = '';
let guiaDebounce = null;
let facturaRelacionadaDebounce = null;

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
    const showPrivado = modalidad === '02';

    $('#bloqueTransportista').toggle(showTransportista);
    $('#bloquePrivado').toggle(showPrivado);
}

function ocultarResultadosFactura() {
    $('#gr_factura_results').addClass('d-none').empty();
}

function limpiarFacturaRelacionada() {
    $('#gr_venta_id').val('');
    $('#gr_factura_search').val('');
    $('#gr_factura_selected').text('Sin factura relacionada');
    ocultarResultadosFactura();
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

        $('#gr_venta_id').val(venta.id);
        $('#gr_factura_search').val(`${venta.numero_comprobante} - ${venta.nombre_cliente || ''}`);
        $('#gr_factura_selected').text(`Factura relacionada: ${venta.numero_comprobante}`);
        ocultarResultadosFactura();

        // Autocompletar destinatario
        $('#gr_dest_tipo_doc').val(venta.tipo_documento_cliente || '6');
        $('#gr_dest_num_doc').val(venta.numero_documento_cliente || '');
        $('#gr_dest_razon_social').val(venta.nombre_cliente || '');

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
        'partida.direccion': 'gr_partida_direccion',
        'llegada.direccion': 'gr_llegada_direccion',
        'transportista.num_doc': 'gr_trans_num_doc',
        'transportista.razon_social': 'gr_trans_razon_social',
        'transportista.reg_mtc': 'gr_trans_reg_mtc',
        'conductor.num_doc': 'gr_cond_num_doc',
        'conductor.nombres': 'gr_cond_nombres',
        'conductor.licencia': 'gr_cond_licencia',
        'vehiculo.placa': 'gr_veh_placa',
        'venta_id': 'gr_factura_search',
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
    setFechasGuia();
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
            tbody.html('<tr><td colspan="9" class="text-center text-muted">No hay guias registradas</td></tr>');
            $('#guiaPaginacion').html('');
            return;
        }

        resp.data.forEach(g => {
            const modalidad = g.modalidad_transporte === '01' ? 'Publico' : 'Privado';
            const tipo = g.tipo_documento === '31' ? '31 - Transportista' : '09 - Remitente';
            const canPdf = g.estado_envio === 'aceptado';
            const canXml = !!g.archivo_xml;
            const reason = (g.mensaje_error || g.descripcion_respuesta_sunat || '').trim();
            tbody.append(`
                <tr>
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning" onclick="editarGuia(${g.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-info" onclick="verGuia(${g.id})" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarGuia(${g.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
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
                    <td><strong>${g.numero_guia}</strong></td>
                    <td>${tipo}</td>
                    <td>${g.destinatario_razon_social}<br><small>${g.destinatario_num_doc}</small></td>
                    <td>${formatDateOnly(g.fecha_traslado)}</td>
                    <td>${modalidad}</td>
                    <td>${Number(g.peso_total || 0).toFixed(3)} ${g.unidad_peso || 'KGM'}</td>
                    <td>
                        ${getBadgeEstado(g.estado_envio)}
                        ${reason ? `<br><small class="text-danger">${escapeHtml(reason)}</small>` : ''}
                    </td>
                </tr>
            `);
        });

        renderPaginacionGuias(resp.pagination);
    } catch (err) {
        $('#guiaTableBody').html('<tr><td colspan="9" class="text-center text-danger">No se pudo cargar guias</td></tr>');
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
        if (g.venta_id && g.venta) {
            $('#gr_factura_search').val(`${g.venta.numero_comprobante} - ${g.venta.nombre_cliente || ''}`);
            $('#gr_factura_selected').text(`Factura relacionada: ${g.venta.numero_comprobante}`);
        } else if (g.venta_id) {
            $('#gr_factura_search').val(`Venta #${g.venta_id}`);
            $('#gr_factura_selected').text(`Factura relacionada: Venta #${g.venta_id}`);
        } else {
            limpiarFacturaRelacionada();
        }
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

        $('#gr_partida_ubigeo').val(g.partida_ubigeo || '');
        $('#gr_partida_direccion').val(g.partida_direccion || '');
        $('#gr_llegada_ubigeo').val(g.llegada_ubigeo || '');
        $('#gr_llegada_direccion').val(g.llegada_direccion || '');

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
        $('#gr_factura_selected').text('Sin factura relacionada');
        const term = $(this).val();
        clearTimeout(facturaRelacionadaDebounce);
        facturaRelacionadaDebounce = setTimeout(() => buscarFacturasRelacionadas(term), 250);
    });

    $('#gr_factura_search').on('focus', function () {
        buscarFacturasRelacionadas($(this).val());
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('#gr_factura_search, #gr_factura_results').length) {
            ocultarResultadosFactura();
        }
    });

    $('#modalGuiaRemision').on('hidden.bs.modal', function () {
        prepararNuevaGuia();
    });
});
</script>
@endpush
