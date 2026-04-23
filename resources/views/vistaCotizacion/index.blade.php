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
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <h3 class="module-title">Cotizaciones</h3>
                                <p class="module-subtitle">Revisa, edita y descarga cotizaciones con el mismo estilo del sistema.</p>
                            </div>
                        </div>
                        <div class="module-header-actions">
                            <button type="button" class="btn btn-success" onclick="window.location.href='/cotizaciones/registro'">
                                <i class="fas fa-plus-circle"></i> Nueva cotizacion
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
                            <input type="text"
                                id="buscador"
                                class="form-control"
                                placeholder="Buscar por cliente, asunto o item...">
                        </div>
                    </div>

                    <div class="module-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover module-table text-center">
                                <thead>
                                    <tr>
                                        <th>Acciones</th>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Asunto</th>
                                        <th>Fecha</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>

                                <tbody id="cotizacionTable">
                                    <tr>
                                        <td colspan="8" class="module-empty">
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
@endsection

@push('scripts')
<script>
let search = '';
let paginaActual = 1;
let debounceBusqueda;

$('#buscador').on('input', function () {
    clearTimeout(debounceBusqueda);

    debounceBusqueda = setTimeout(() => {
        search = $(this).val().trim();
        fetchCotizaciones(1);
    }, 300);
});

function normalizeCurrency(code) {
    return String(code || 'PEN').toUpperCase() === 'USD' ? 'USD' : 'PEN';
}

function currencySymbol(code) {
    return normalizeCurrency(code) === 'USD' ? 'US$' : 'S/';
}

function formatCurrency(value, code = 'PEN') {
    return `${currencySymbol(code)} ${Number(value || 0).toFixed(2)}`;
}

function formatDate(value) {
    if (!value) {
        return '-';
    }

    const date = new Date(`${value}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('es-PE');
}

function renderEstado(estado) {
    const colors = {
        borrador: 'secondary',
        aprobado: 'success',
        rechazado: 'danger'
    };

    const badgeClass = colors[estado] || 'light';
    return `<span class="badge badge-${badgeClass} text-uppercase">${estado || 'sin estado'}</span>`;
}

function renderPaginacion(pagination) {
    if (!pagination || pagination.last_page <= 1) {
        $('#paginacion').html('');
        return;
    }

    let html = '';

    for (let page = 1; page <= pagination.last_page; page++) {
        html += `
            <button type="button" class="btn btn-sm ${page === pagination.current_page ? 'btn-primary' : 'btn-light'}"
                onclick="fetchCotizaciones(${page})">
                ${page}
            </button>
        `;
    }

    $('#paginacion').html(html);
}

function fetchCotizaciones(page = 1) {
    paginaActual = page;

    $('#cotizacionTable').html(`
        <tr>
            <td colspan="8" class="module-empty">
                <div class="spinner-border text-primary"></div>
            </td>
        </tr>
    `);

    apiFetch(`/api/cotizaciones?search=${encodeURIComponent(search)}&page=${page}`)
        .then(resp => {
            if (!resp.data.length) {
                $('#cotizacionTable').html(`
                    <tr>
                        <td colspan="8" class="module-empty">No se encontraron cotizaciones</td>
                    </tr>
                `);
                $('#paginacion').html('');
                return;
            }

            let html = '';

            resp.data.forEach(cotizacion => {
                html += `
                    <tr>
                        <td>
                            <div class="table-action-group">
                                <a href="/cotizaciones/registro?id=${cotizacion.id}" class="btn btn-soft-warning btn-sm" title="Editar cotizacion">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-soft-primary btn-sm" onclick="verPdf(${cotizacion.id})" title="Ver PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button type="button" class="btn btn-soft-danger btn-sm" onclick="eliminar(${cotizacion.id})" title="Eliminar cotizacion">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                        <td>#${cotizacion.id}</td>
                        <td class="text-left">${cotizacion.cliente?.razon_social || '-'}</td>
                        <td class="text-left">${cotizacion.asunto || '-'}</td>
                        <td>${formatDate(cotizacion.fecha)}</td>
                        <td>
                            <span class="badge badge-info">
                                ${cotizacion.detalles_count ?? 0}
                            </span>
                        </td>
                        <td><strong>${formatCurrency(cotizacion.total, cotizacion.moneda)}</strong></td>
                        <td>${renderEstado(cotizacion.estado)}</td>
                    </tr>
                `;
            });

            $('#cotizacionTable').html(html);
            renderPaginacion(resp.pagination);
        })
        .catch(err => {
            $('#cotizacionTable').html(`
                <tr>
                    <td colspan="8" class="module-empty text-danger">No se pudieron cargar las cotizaciones</td>
                </tr>
            `);

            Swal.fire('Error', err.message || 'No se pudo cargar cotizaciones', 'error');
        });
}

function eliminar(id) {
    Swal.fire({
        title: 'Eliminar cotizacion?',
        text: 'Esta accion no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (!result.isConfirmed) {
            return;
        }

        apiFetch(`/api/cotizaciones/${id}`, {
            method: 'DELETE'
        })
            .then(resp => {
                Swal.fire('OK', resp.message, 'success');
                fetchCotizaciones(paginaActual);
            })
            .catch(err => {
                Swal.fire('Error', err.message || 'No se pudo eliminar', 'error');
            });
    });
}

function verPdf(id) {
    window.open(`/cotizaciones/pdf/${id}`, '_blank');
}

$(document).ready(() => {
    fetchCotizaciones();
});
</script>
@endpush
