@extends('admin.main')

@section('contenido')

<div class="content">
<div class="container-fluid">

<div class="card shadow">
<div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="m-0"><i class="fas fa-file-alt"></i> Notas de Credito y Debito</h5>
    <button class="btn btn-primary" data-toggle="modal" data-target="#modalNota">
        Generar Nota
    </button>
</div>

<div class="card-body">
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-dark">
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
                    <th width="170">Opciones</th>
                </tr>
            </thead>
            <tbody id="tablaNotas"></tbody>
        </table>
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
    <button class="btn btn-success" onclick="procesarNota()">Registrar Nota</button>
    <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
</div>

</div>
</div>
</div>

@endsection

@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', cargarNotas);

function badgeEstadoNota(estado) {
    if (estado === 'aceptado') return '<span class="badge badge-success">Aceptado</span>';
    if (estado === 'rechazado') return '<span class="badge badge-danger">Rechazado</span>';
    if (estado === 'procesando') return '<span class="badge badge-info">Procesando</span>';
    if (estado === 'error') return '<span class="badge badge-dark">Error</span>';
    return '<span class="badge badge-warning">Pendiente</span>';
}

function cargarNotas() {
    apiFetch('/api/facturacion/notas')
        .then((data) => {
            const tbody = document.getElementById('tablaNotas');
            const lista = data.data || [];
            tbody.innerHTML = '';

            if (!lista.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center text-muted">No hay notas registradas</td>
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
                            <button class="btn btn-sm btn-info" title="Ver PDF"
                                ${canUsePdf ? '' : 'disabled'}
                                onclick="${canUsePdf ? `verPdfNota(${n.id})` : 'return false;'}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Descargar PDF"
                                ${canUsePdf ? '' : 'disabled'}
                                onclick="${canUsePdf ? `descargarPdfNota(${n.id})` : 'return false;'}">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary" title="Ver XML"
                                ${canUseXml ? '' : 'disabled'}
                                onclick="${canUseXml ? `verXmlNota(${n.id})` : 'return false;'}">
                                <i class="fas fa-code"></i>
                            </button>
                            <button class="btn btn-sm btn-dark" title="Descargar XML"
                                ${canUseXml ? '' : 'disabled'}
                                onclick="${canUseXml ? `descargarXmlNota(${n.id})` : 'return false;'}">
                                <i class="fas fa-file-download"></i>
                            </button>
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

$(document).ready(function() {
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
