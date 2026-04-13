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
                                <i class="fas fa-route"></i>
                            </div>
                            <div>
                                <h3 class="module-title">Rutas</h3>
                                <p class="module-subtitle">Controla viajes, costos operativos y accesos directos a viaticos, combustible y peajes por ruta.</p>
                            </div>
                        </div>

                        <div class="module-header-actions">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalRegistroRuta">
                                <i class="fas fa-plus-circle"></i> Nueva ruta
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
                                   placeholder="Buscar por origen, destino, conductor o placa...">
                        </div>
                    </div>

                    <div class="module-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover module-table text-center">
                                <thead>
                                    <tr>
                                        <th>Acciones</th>
                                        <th>ID</th>
                                        <th>Viaje</th>
                                        <th>Unidad</th>
                                        <th>Gastos</th>
                                        <th>Estado</th>
                                        <th>Resultado</th>
                                        <th>Reporte</th>
                                    </tr>
                                </thead>
                                <tbody id="rutaTableBody">
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

@include('ruta.registro')
@endsection

@push('scripts')
<script>
let searchGlobal = '';
let debounceTimer;
let paginaActual = 1;

$('#buscador').on('input', function () {
    clearTimeout(debounceTimer);
    const texto = $(this).val().trim();

    debounceTimer = setTimeout(() => {
        searchGlobal = texto;
        fetchRutas(1);
    }, 300);
});

function formatMoney(value) {
    return `S/ ${Number(value || 0).toFixed(2)}`;
}

function renderEstadoBadge(estado) {
    const normalized = String(estado || '').toLowerCase();

    switch (normalized) {
        case 'pendiente':
            return '<span class="badge badge-secondary">Pendiente</span>';
        case 'en curso':
            return '<span class="badge badge-warning">En curso</span>';
        case 'finalizado':
            return '<span class="badge badge-success">Finalizado</span>';
        case 'cancelado':
            return '<span class="badge badge-danger">Cancelado</span>';
        default:
            return '<span class="badge badge-light">Sin estado</span>';
    }
}

function renderPaginacion(pagination) {
    if (!pagination || pagination.last_page <= 1) {
        $('#paginacion').html('');
        return;
    }

    let html = '';

    for (let i = 1; i <= pagination.last_page; i++) {
        html += `
            <button type="button"
                    class="btn btn-sm ${i === pagination.current_page ? 'btn-primary' : 'btn-light'}"
                    onclick="fetchRutas(${i})">
                ${i}
            </button>
        `;
    }

    $('#paginacion').html(html);
}

function fetchRutas(page = 1) {
    paginaActual = page;

    $('#rutaTableBody').html(`
        <tr>
            <td colspan="8" class="module-empty">
                <div class="spinner-border text-primary"></div>
            </td>
        </tr>
    `);

    apiFetch(`/api/rutas?search=${encodeURIComponent(searchGlobal)}&page=${page}`)
        .then(resp => {
            const rutas = resp.data || [];

            if (!rutas.length) {
                $('#rutaTableBody').html(`
                    <tr>
                        <td colspan="8" class="module-empty">No se encontraron rutas.</td>
                    </tr>
                `);
                $('#paginacion').html('');
                return;
            }

            let html = '';

            rutas.forEach(ruta => {
                const resumen = ruta.resumen_financiero || {};
                const conductor = `${ruta.conductor?.nombre || ''} ${ruta.conductor?.apellido || ''}`.trim() || 'Sin conductor';
                const tracto = ruta.camion?.placa_tracto || ruta.conductor?.camion?.placa_tracto || '-';
                const trailer = ruta.camion?.placa_carreto || ruta.conductor?.camion?.placa_carreto || '-';
                const margen = resumen.margen_pct === null || resumen.margen_pct === undefined
                    ? 'N/D'
                    : `${Number(resumen.margen_pct).toFixed(2)}%`;
                const utilidadClass = Number(resumen.utilidad || 0) >= 0 ? 'text-success' : 'text-danger';

                html += `
                    <tr id="ruta_${ruta.id}">
                        <td>
                            <div class="table-action-group">
                                <button type="button" class="btn btn-soft-warning btn-sm" onclick="editar(${ruta.id})" title="Editar ruta">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-soft-danger btn-sm" onclick="eliminar(${ruta.id})" title="Eliminar ruta">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                        <td><strong>#${ruta.id}</strong></td>
                        <td class="text-left">
                            <strong>${ruta.origen || '-'}</strong> <i class="fas fa-arrow-right text-muted mx-1"></i> <strong>${ruta.destino || '-'}</strong><br>
                            <small class="text-muted">Salida: ${ruta.fecha_inicio || '-'} | Llegada: ${ruta.fecha_fin || '-'}</small><br>
                            <small class="text-muted">Conductor: ${conductor}</small>
                        </td>
                        <td class="text-left">
                            <strong>Tracto:</strong> ${tracto}<br>
                            <strong>Trailer:</strong> ${trailer}<br>
                            <div class="table-action-group mt-2">
                                <button type="button" class="btn btn-soft-primary btn-sm" onclick="registrarViaticos(${ruta.id})">
                                    <i class="fas fa-wallet"></i>
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm" onclick="registrarPeajes(${ruta.id})">
                                    <i class="fas fa-road"></i>
                                </button>
                                <button type="button" class="btn btn-soft-dark btn-sm" onclick="registrarCombustible(${ruta.id})">
                                    <i class="fas fa-gas-pump"></i>
                                </button>
                            </div>
                        </td>
                        <td class="text-left">
                            <strong>Viaticos:</strong> ${formatMoney(resumen.viaticos)}<br>
                            <strong>Peajes:</strong> ${formatMoney(resumen.peajes)}<br>
                            <strong>Combustible:</strong> ${formatMoney(resumen.combustible)}<br>
                            <strong>Total:</strong> ${formatMoney(resumen.gastos)}
                        </td>
                        <td>${renderEstadoBadge(ruta.estado)}</td>
                        <td class="text-left">
                            <strong>Ingreso:</strong> ${formatMoney(resumen.ingresos)}<br>
                            <strong>Gasto:</strong> ${formatMoney(resumen.gastos)}<br>
                            <strong class="${utilidadClass}">Utilidad: ${formatMoney(resumen.utilidad)}</strong><br>
                            <small class="text-muted">Margen: ${margen}</small>
                        </td>
                        <td>
                            <div class="table-action-group">
                                <a href="/reportes/rutas/${ruta.id}" class="btn btn-soft-primary btn-sm" title="Ver reporte">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <button type="button" class="btn btn-soft-danger btn-sm" onclick="window.open('/reportes/rutas/${ruta.id}/pdf', '_blank')" title="Descargar PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            $('#rutaTableBody').html(html);
            renderPaginacion(resp.pagination);
        })
        .catch(err => {
            $('#rutaTableBody').html(`
                <tr>
                    <td colspan="8" class="module-empty text-danger">No se pudieron cargar las rutas.</td>
                </tr>
            `);

            Swal.fire('Error', err.message || 'No se pudieron cargar las rutas', 'error');
        });
}

function eliminar(id) {
    Swal.fire({
        title: 'Eliminar ruta?',
        text: 'Esta accion no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar'
    }).then(result => {
        if (!result.isConfirmed) {
            return;
        }

        apiFetch(`/api/rutas/${id}`, {
            method: 'DELETE'
        }).then(resp => {
            Swal.fire('OK', resp.message, 'success');
            fetchRutas(paginaActual);
        }).catch(err => {
            Swal.fire('Error', err.message || 'No se pudo eliminar la ruta', 'error');
        });
    });
}

function registrarViaticos(id) {
    window.location.href = `/ruta/${id}/rutaviatico`;
}

function registrarPeajes(id) {
    window.location.href = `/ruta/${id}/rutapeaje`;
}

function registrarCombustible(id) {
    window.location.href = `/ruta/${id}/rutacombustible`;
}

$(document).ready(() => {
    fetchRutas();
});
</script>
@endpush
