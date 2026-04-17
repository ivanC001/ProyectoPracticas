@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="module-shell">
            <div class="card module-card">
                <div class="module-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="module-heading">
                            <div class="module-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div>
                                <h3 class="module-title">Notas de Credito y Debito</h3>
                                <p class="module-subtitle">Controla notas emitidas, revisa respuestas de SUNAT y abre sus archivos desde una sola tabla.</p>
                            </div>
                        </div>
                        <div class="module-header-actions">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalNota">
                                <i class="fas fa-plus-circle"></i> Generar Nota
                            </button>
                        </div>
                    </div>
                </div>

                <div class="module-body">
                    <div id="notaFiltroInfo" class="alert alert-info py-2 px-3 mb-3" style="display:none;"></div>
                    <div class="module-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover module-table table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nota</th>
                                        <th>Tipo</th>
                                        <th>Factura Afectada</th>
                                        <th>Cliente</th>
                                        <th>Emitido por</th>
                                        <th>Monto</th>
                                        <th>Motivo</th>
                                        <th>Estado</th>
                                        <th>Respuesta SUNAT</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaNotas">
                                    <tr>
                                        <td colspan="11" class="module-empty">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNota" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nueva Nota de Credito / Debito</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                @include('NotasCredito.registro')
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="procesarNota()">Registrar Nota</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const notaQueryParams = new URLSearchParams(window.location.search);
const notaFiltroVentaId = notaQueryParams.get('venta_id');
const notaFiltroFactura = notaQueryParams.get('factura');

document.addEventListener('DOMContentLoaded', cargarNotas);

function badgeEstadoNota(estado) {
    if (estado === 'aceptado') return '<span class="badge badge-success">Aceptado</span>';
    if (estado === 'rechazado') return '<span class="badge badge-danger">Rechazado</span>';
    if (estado === 'procesando') return '<span class="badge badge-info">Procesando</span>';
    if (estado === 'error') return '<span class="badge badge-dark">Error</span>';
    return '<span class="badge badge-warning">Pendiente</span>';
}

function cargarNotas() {
    const endpoint = notaFiltroVentaId
        ? `/api/facturacion/notas?venta_id=${encodeURIComponent(notaFiltroVentaId)}`
        : '/api/facturacion/notas';

    if (notaFiltroVentaId) {
        const info = document.getElementById('notaFiltroInfo');
        info.style.display = 'block';
        info.innerHTML = `
            Mostrando notas relacionadas a la factura:
            <strong>${notaFiltroFactura || `ID ${notaFiltroVentaId}`}</strong>
            <a href="/notascredito" class="ml-2">Ver todas</a>
        `;
    }

    apiFetch(endpoint)
        .then((data) => {
            const tbody = document.getElementById('tablaNotas');
            const lista = data.data || [];
            tbody.innerHTML = '';

            if (!lista.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="module-empty">No hay notas registradas</td>
                    </tr>
                `;
                return;
            }

            lista.forEach((n) => {
                const tipo = n.tipo_documento === '07' ? 'Credito' : 'Debito';
                const reason = n.descripcion_respuesta_sunat || n.mensaje_error || '-';
                const code = n.codigo_respuesta_sunat ? `<small class="text-muted">[${n.codigo_respuesta_sunat}]</small> ` : '';
                const canUsePdf = n.estado_envio === 'aceptado' && !!n.archivo_pdf;
                const canUseXml = !!n.archivo_xml;
                const simbolo = (n.venta_moneda || 'PEN') === 'USD' ? 'US$' : 'S/';

                tbody.innerHTML += `
                    <tr>
                        <td>${n.id}</td>
                        <td><strong>${n.numero_comprobante}</strong></td>
                        <td>${tipo}</td>
                        <td>${n.factura_afectada || '-'}</td>
                        <td>${n.cliente || '-'}</td>
                        <td>${n.emisor_nombre || 'Sin usuario'}</td>
                        <td>${simbolo} ${Number(n.total || 0).toFixed(2)}</td>
                        <td><small>${n.codMotivo || ''}</small> ${n.desMotivo || ''}</td>
                        <td>${badgeEstadoNota(n.estado_envio)}</td>
                        <td>${code}${reason}</td>
                        <td class="text-center">
                            <div class="table-action-group">
                                <button type="button" class="btn btn-soft-primary btn-sm" title="Ver PDF" ${canUsePdf ? '' : 'disabled'} onclick="${canUsePdf ? `verPdfNota(${n.id})` : 'return false;'}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-soft-danger btn-sm" title="Descargar PDF" ${canUsePdf ? '' : 'disabled'} onclick="${canUsePdf ? `descargarPdfNota(${n.id})` : 'return false;'}">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm" title="Ver XML" ${canUseXml ? '' : 'disabled'} onclick="${canUseXml ? `verXmlNota(${n.id})` : 'return false;'}">
                                    <i class="fas fa-code"></i>
                                </button>
                                <button type="button" class="btn btn-soft-dark btn-sm" title="Descargar XML" ${canUseXml ? '' : 'disabled'} onclick="${canUseXml ? `descargarXmlNota(${n.id})` : 'return false;'}">
                                    <i class="fas fa-file-download"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        })
        .catch((err) => {
            console.error('ERROR NOTAS:', err);
        });
}

function getNotaPdfUrl(id) {
    return `/notas/pdf/${id}?_t=${Date.now()}`;
}

function getNotaPdfDownloadUrl(id) {
    return `/notas/pdf/${id}/descargar?_t=${Date.now()}`;
}

function getNotaXmlUrl(id) {
    return `/notas/xml/${id}?_t=${Date.now()}`;
}

function getNotaXmlDownloadUrl(id) {
    return `/notas/xml/${id}/descargar?_t=${Date.now()}`;
}

function verPdfNota(id) {
    window.open(getNotaPdfUrl(id), '_blank');
}

function descargarPdfNota(id) {
    window.open(getNotaPdfDownloadUrl(id), '_blank');
}

function verXmlNota(id) {
    window.open(getNotaXmlUrl(id), '_blank');
}

function descargarXmlNota(id) {
    window.open(getNotaXmlDownloadUrl(id), '_blank');
}

$(document).ready(function () {
    $('#modalNota').on('shown.bs.modal', function () {
        if (typeof cargarFacturasEmitidasNota === 'function') {
            cargarFacturasEmitidasNota();
        }
    });

    $('#modalNota').on('hidden.bs.modal', function () {
        cargarNotas();
    });
});
</script>
@endpush
