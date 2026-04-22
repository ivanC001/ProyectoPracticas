@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <style>
            #tablaFacturasVentas th,
            #tablaFacturasVentas td {
                vertical-align: middle;
            }

            #tablaFacturasVentas .col-importe {
                min-width: 120px;
                text-align: right;
                font-weight: 700;
                white-space: nowrap;
            }

            #tablaFacturasVentas .col-opciones {
                min-width: 205px;
            }
        </style>
        <div class="module-shell">
            <div class="card module-card">
                <div class="module-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="module-heading">
                            <div class="module-icon">
                                <i class="fas fa-cash-register"></i>
                            </div>
                            <div>
                                <h3 class="module-title">Ventas</h3>
                                <p class="module-subtitle">Consulta comprobantes emitidos y registra nuevas facturas o boletas desde una sola vista.</p>
                            </div>
                        </div>
                        <div class="module-header-actions">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalFactura">
                                <i class="fas fa-plus-circle"></i> Nueva Factura
                            </button>
                        </div>
                    </div>
                </div>

                <div class="module-body">
                    <div class="module-search mb-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" id="buscar" class="form-control" placeholder="Buscar por cliente, comprobante o documento...">
                        </div>
                    </div>

                    <div class="module-table-wrap">
                        <div class="table-responsive">
                            <table id="tablaFacturasVentas" class="table table-hover module-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Comprobante</th>
                                        <th>Cliente</th>
                                        <th>Documento</th>
                                        <th>Fecha</th>
                                        <th>Importe</th>
                                        <th id="thEmisor">Emitido por</th>
                                        <th>Estado</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody id="facturaTableBody">
                                    <tr>
                                        <td colspan="9" class="module-empty">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="paginacion" class="module-pagination mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPdfFactura" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-pdf"></i> Vista previa de factura
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body p-0" style="height:80vh;">
                <iframe id="pdfFacturaFrame" src="about:blank" style="width:100%;height:100%;border:0;"></iframe>
            </div>

            <div class="modal-footer justify-content-between">
                <small class="text-muted" id="pdfFacturaHint">Selecciona una factura para visualizar su PDF.</small>
                <div>
                    <a id="btnDescargarPdfFactura" class="btn btn-danger" href="#" target="_blank" rel="noopener">
                        <i class="fas fa-download"></i> Descargar PDF
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFactura" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nueva Factura</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                @include('factura.registro')
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnGuardarFactura" onclick="procesarFactura()">
                    Registrar Factura
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let searchGlobal = '';
let facturaPdfActual = null;
const showEmisorColumn = getCurrentRole() === 'admin';
const facturaTableColspan = showEmisorColumn ? 9 : 8;
const FACTURA_STATUS_ACTIVE = ['pendiente', 'procesando'];
let currentFacturasPage = 1;
let facturaHasActiveProcessing = false;
let facturaAutoRefreshTimer = null;
let facturaSnapshot = '';

function getCurrentRole() {
    try {
        const user = JSON.parse(localStorage.getItem('auth_user') || 'null');
        return user?.rol || '';
    } catch (error) {
        return '';
    }
}

function encodeParam(value) {
    return encodeURIComponent(String(value ?? ''));
}

function decodeParam(value) {
    try {
        return decodeURIComponent(String(value ?? ''));
    } catch (e) {
        return String(value ?? '');
    }
}

function applyFacturaColumnsByRole() {
    const emisorHeader = document.getElementById('thEmisor');
    const placeholderCell = document.querySelector('#facturaTableBody td[colspan]');

    if (emisorHeader) {
        emisorHeader.style.display = showEmisorColumn ? '' : 'none';
    }

    if (placeholderCell) {
        placeholderCell.colSpan = facturaTableColspan;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    applyFacturaColumnsByRole();
    cargarFacturas(1, { forceRender: true });
    startFacturaAutoRefresh();
});

document.getElementById('buscar').addEventListener('keyup', function () {
    searchGlobal = this.value;
    cargarFacturas(1, { forceRender: true });
});

function buildFacturasSnapshot(data) {
    const lista = data?.data || [];
    const meta = [
        data?.current_page || 1,
        data?.last_page || 1,
        data?.total || 0,
    ].join('|');

    const rows = lista.map((f) => [
        f.id,
        f.estado_envio || '',
        Number(f.sunat_enviado || 0),
        Number(f.notas_credito_count || 0),
        Number(Boolean(f.archivo_pdf)),
        Number(Boolean(f.archivo_xml)),
        f.codigo_respuesta_sunat || '',
    ].join(':')).join('|');

    return `${meta}::${rows}`;
}

function updateFacturaRefreshFlag(lista) {
    facturaHasActiveProcessing = lista.some((f) => FACTURA_STATUS_ACTIVE.includes(String(f.estado_envio || '').toLowerCase()));
}

function nextFacturaRefreshDelay() {
    return facturaHasActiveProcessing ? 7000 : 20000;
}

async function cargarFacturas(page = 1, options = {}) {
    const { background = false, forceRender = false } = options;
    currentFacturasPage = page;

    try {
        const data = await apiFetch(`/api/facturas?page=${page}&search=${encodeURIComponent(searchGlobal)}`);
        const lista = data.data || [];
        const nextSnapshot = buildFacturasSnapshot(data);

        updateFacturaRefreshFlag(lista);

        if (background && !forceRender && nextSnapshot === facturaSnapshot) {
            return;
        }

        facturaSnapshot = nextSnapshot;

        const tbody = document.getElementById('facturaTableBody');
        tbody.innerHTML = '';

        if (!lista.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${facturaTableColspan}" class="module-empty">No hay resultados</td>
                </tr>`;
            document.getElementById('paginacion').innerHTML = '';
            return;
        }

        lista.forEach((f) => {
                let estado = '<span class="badge badge-warning">Pendiente</span>';

                if (f.estado_envio === 'aceptado') {
                    estado = '<span class="badge badge-success">Aceptado</span>';
                } else if (f.estado_envio === 'rechazado') {
                    estado = '<span class="badge badge-danger">Rechazado</span>';
                } else if (f.estado_envio === 'procesando') {
                    estado = '<span class="badge badge-info">Procesando</span>';
                } else if (f.estado_envio === 'error') {
                    estado = '<span class="badge badge-dark">Error</span>';
                }

                const numeroComprobante = (f.numero_comprobante || '').replace(/'/g, "\\'");
                const canUsePdf = f.estado_envio === 'aceptado' && !!f.archivo_pdf;
                const canUseXml = !!f.archivo_xml;
                const canRetry = ['error', 'pendiente'].includes(f.estado_envio) && !f.sunat_enviado;
                const canDuplicate = f.estado_envio === 'rechazado';
                const hasErrorInfo = ['rechazado', 'error'].includes(f.estado_envio)
                    || !!(f.descripcion_respuesta_sunat || f.mensaje_error || f.codigo_respuesta_sunat);
                const rejectionCode = encodeParam(f.codigo_respuesta_sunat || '');
                const rejectionDesc = encodeParam(f.descripcion_respuesta_sunat || '');
                const technicalError = encodeParam(f.mensaje_error || '');
                const emisorNombre = (f.emisor && f.emisor.name) ? f.emisor.name : 'Sin usuario';
                const notaCreditoCount = Number(f.notas_credito_count || 0);
                const hasNotaCredito = notaCreditoCount > 0;
                const monedaSimbolo = f.moneda === 'USD' ? 'US$' : 'S/';
                const importeCell = `${monedaSimbolo} ${parseFloat(f.total_venta || 0).toFixed(2)}`;
                const retryButton = canRetry
                    ? `<button type="button" class="btn btn-soft-warning btn-sm" title="Reintentar envio" onclick="reintentarFactura(${f.id})">
                            <i class="fas fa-redo-alt"></i>
                       </button>`
                    : '';
                const duplicateButton = canDuplicate
                    ? `<button type="button" class="btn btn-soft-primary btn-sm" title="Corregir y duplicar" onclick="corregirFacturaRechazada(${f.id}, '${numeroComprobante}')">
                            <i class="fas fa-clone"></i>
                       </button>`
                    : '';
                const emisorCell = showEmisorColumn ? `<td>${emisorNombre}</td>` : '';

                tbody.innerHTML += `
                    <tr>
                        <td>${f.id}</td>
                        <td><strong>${f.numero_comprobante}</strong></td>
                        <td>${f.nombre_cliente}</td>
                        <td>${f.numero_documento_cliente}</td>
                        <td>${formatearFecha(f.fecha_emision)}</td>
                        <td class="col-importe">${importeCell}</td>
                        ${emisorCell}
                        <td>${estado}</td>
                        <td class="text-center col-opciones">
                            <div class="table-action-group">
                                <button type="button" class="btn btn-soft-primary btn-sm" title="Ver PDF" ${canUsePdf ? '' : 'disabled'} onclick="${canUsePdf ? `mostrarPdf(${f.id}, '${numeroComprobante}')` : 'return false;'}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-soft-danger btn-sm" title="Descargar PDF" ${canUsePdf ? '' : 'disabled'} onclick="${canUsePdf ? `descargarPdf(${f.id})` : 'return false;'}">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm" title="Ver XML" ${canUseXml ? '' : 'disabled'} onclick="${canUseXml ? `verXml(${f.id})` : 'return false;'}">
                                    <i class="fas fa-code"></i>
                                </button>
                                <button type="button" class="btn btn-soft-dark btn-sm" title="Descargar XML" ${canUseXml ? '' : 'disabled'} onclick="${canUseXml ? `descargarXml(${f.id})` : 'return false;'}">
                                    <i class="fas fa-file-download"></i>
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm" title="Ver motivo" ${hasErrorInfo ? '' : 'disabled'} onclick="${hasErrorInfo ? `verMotivoRechazo(${f.id}, '${rejectionCode}', '${rejectionDesc}', '${technicalError}', '${f.estado_envio || ''}')` : 'return false;'}">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                ${retryButton}
                                ${duplicateButton}
                                <button type="button" class="btn btn-soft-success btn-sm" title="Ver notas de credito" ${hasNotaCredito ? '' : 'disabled'} onclick="${hasNotaCredito ? `verNotasCreditoFactura(${f.id}, '${numeroComprobante}')` : 'return false;'}">
                                    <i class="fas fa-sticky-note"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
        });

        renderPaginacion(data);
    } catch (err) {
        if (!background) {
            console.error('ERROR FACTURAS:', err);
        }
    }
}

function stopFacturaAutoRefresh() {
    if (facturaAutoRefreshTimer) {
        clearTimeout(facturaAutoRefreshTimer);
        facturaAutoRefreshTimer = null;
    }
}

function scheduleFacturaAutoRefresh() {
    stopFacturaAutoRefresh();

    facturaAutoRefreshTimer = setTimeout(async () => {
        if (document.visibilityState === 'visible') {
            await cargarFacturas(currentFacturasPage, { background: true });
        }
        scheduleFacturaAutoRefresh();
    }, nextFacturaRefreshDelay());
}

function startFacturaAutoRefresh() {
    scheduleFacturaAutoRefresh();
}

function renderPaginacion(data) {
    if (!data || data.last_page <= 1) {
        document.getElementById('paginacion').innerHTML = '';
        return;
    }

    let html = '';

    for (let i = 1; i <= data.last_page; i++) {
        html += `
            <button type="button" class="btn btn-sm ${i === data.current_page ? 'btn-primary' : 'btn-light'}" onclick="cargarFacturas(${i})">
                ${i}
            </button>
        `;
    }

    document.getElementById('paginacion').innerHTML = html;
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleString('es-PE');
}

function getPdfUrl(id) {
    return `/factura/pdf/${id}?_t=${Date.now()}`;
}

function getPdfDownloadUrl(id) {
    return `/factura/pdf/${id}/descargar?_t=${Date.now()}`;
}

function getXmlUrl(id) {
    return `/factura/xml/${id}`;
}

function getXmlDownloadUrl(id) {
    return `/factura/xml/${id}/descargar`;
}

function mostrarPdf(id, numeroComprobante = '') {
    facturaPdfActual = id;

    document.getElementById('pdfFacturaFrame').src = getPdfUrl(id);
    document.getElementById('btnDescargarPdfFactura').href = getPdfDownloadUrl(id);
    document.getElementById('pdfFacturaHint').innerText = numeroComprobante
        ? `Factura ${numeroComprobante}`
        : `Factura #${id}`;

    $('#modalPdfFactura').modal('show');
}

function descargarPdf(id) {
    window.open(getPdfDownloadUrl(id), '_blank');
}

function verXml(id) {
    window.open(getXmlUrl(id), '_blank');
}

function descargarXml(id) {
    window.open(getXmlDownloadUrl(id), '_blank');
}

function verMotivoRechazo(id, codigo = '', descripcion = '', mensajeError = '', estado = '') {
    const decCode = decodeParam(codigo);
    const decDescripcion = decodeParam(descripcion);
    const decMensajeError = decodeParam(mensajeError);

    const codigoMsg = decCode ? `<strong>Codigo:</strong> ${decCode}<br>` : '';
    const detalle = decDescripcion || decMensajeError || 'No hay detalle registrado en la base de datos.';
    const estadoMsg = estado ? `<strong>Estado:</strong> ${estado}<br>` : '';
    const ayuda = (!decDescripcion && !decMensajeError)
        ? '<br><small class="text-muted">Revisa el log del servidor en <code>storage/logs/laravel.log</code>.</small>'
        : '';

    Swal.fire({
        icon: 'info',
        title: `Detalle de estado #${id}`,
        html: `${estadoMsg}${codigoMsg}<strong>Motivo:</strong> ${detalle}${ayuda}`,
        confirmButtonText: 'Entendido'
    });
}

function reintentarFactura(id) {
    Swal.fire({
        title: 'Reintentar envio?',
        text: 'Se volvera a enviar la misma factura a SUNAT.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Si, reintentar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (!result.isConfirmed) {
            return;
        }

        apiFetch(`/api/facturas/${id}/reintentar`, {
            method: 'POST'
        })
        .then(resp => {
            Swal.fire('OK', resp.message || 'Factura enviada nuevamente a proceso.', 'success');
            cargarFacturas();
        })
        .catch(err => {
            Swal.fire('Error', err.message || 'No se pudo reintentar el envio.', 'error');
        });
    });
}

function corregirFacturaRechazada(id, numeroComprobante = '') {
    apiFetch(`/api/facturas/${id}/duplicar-rechazada`)
        .then(resp => {
            const payload = resp.payload || null;

            if (!payload) {
                Swal.fire('Error', 'No se encontro informacion para duplicar.', 'error');
                return;
            }

            if (typeof window.cargarFacturaDesdePayload !== 'function') {
                Swal.fire('Error', 'No se pudo cargar el formulario de factura.', 'error');
                return;
            }

            window.cargarFacturaDesdePayload(payload);
            $('#modalFactura').modal('show');

            const motivo = resp.motivo_rechazo || 'Sin detalle de rechazo.';
            Swal.fire({
                icon: 'info',
                title: 'Factura cargada para correccion',
                html: `
                    <strong>Original:</strong> ${numeroComprobante || resp.numero_original || '-'}<br>
                    <strong>Motivo:</strong> ${motivo}<br><br>
                    Se generara un nuevo comprobante con nuevo correlativo.
                `,
                confirmButtonText: 'Continuar'
            });
        })
        .catch(err => {
            Swal.fire('Error', err.message || 'No se pudo cargar la factura rechazada.', 'error');
        });
}

function verNotasCreditoFactura(ventaId, numeroComprobante = '') {
    const target = `/notascredito?venta_id=${ventaId}&factura=${encodeURIComponent(numeroComprobante || '')}`;
    window.location.href = target;
}

$(document).ready(function () {
    $('#modalFactura').on('shown.bs.modal', function () {
        fetchProductos();
        setFechaActual();
    });

    $('#modalFactura').on('hidden.bs.modal', function () {
        if (typeof limpiarFormularioFactura === 'function') {
            limpiarFormularioFactura();
        }
        cargarFacturas(currentFacturasPage, { forceRender: true });
    });

    $('#modalPdfFactura').on('hidden.bs.modal', function () {
        document.getElementById('pdfFacturaFrame').src = 'about:blank';
        document.getElementById('btnDescargarPdfFactura').href = '#';
        document.getElementById('pdfFacturaHint').innerText = 'Selecciona una factura para visualizar su PDF.';
        facturaPdfActual = null;
    });
});

document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible') {
        cargarFacturas(currentFacturasPage, { background: true });
    }
});

window.addEventListener('beforeunload', stopFacturaAutoRefresh);
</script>
@endpush
