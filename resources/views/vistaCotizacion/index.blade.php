@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">
                        <i class="fas fa-file-invoice-dollar text-primary"></i> Cotizaciones
                    </h4>
                    <small class="text-muted">Listado de cotizaciones registradas</small>
                </div>

                <a href="/cotizaciones/registro" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nueva Cotizacion
                </a>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <div class="input-group shadow-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text"
                            id="buscador"
                            class="form-control"
                            placeholder="Buscar por cliente, asunto o item...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped text-center">
                        <thead class="bg-primary text-white">
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
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-primary"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="paginacion" class="mt-3 text-center"></div>
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

function formatCurrency(value) {
    return `S/ ${Number(value || 0).toFixed(2)}`;
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
            <button class="btn btn-sm ${page === pagination.current_page ? 'btn-primary' : 'btn-light'} mr-1"
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
            <td colspan="8" class="text-center">
                <div class="spinner-border text-primary"></div>
            </td>
        </tr>
    `);

    apiFetch(`/api/cotizaciones?search=${encodeURIComponent(search)}&page=${page}`)
        .then(resp => {
            if (!resp.data.length) {
                $('#cotizacionTable').html(`
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No se encontraron cotizaciones
                        </td>
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
                            <a href="/cotizaciones/registro?id=${cotizacion.id}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>

                            <button class="btn btn-info btn-sm" onclick="verPdf(${cotizacion.id})">
                                <i class="fas fa-file-pdf"></i>
                            </button>

                            <button class="btn btn-danger btn-sm" onclick="eliminar(${cotizacion.id})">
                                <i class="fas fa-trash"></i>
                            </button>
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
                        <td><strong>${formatCurrency(cotizacion.total)}</strong></td>
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
                    <td colspan="8" class="text-center text-danger">
                        No se pudieron cargar las cotizaciones
                    </td>
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
