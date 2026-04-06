@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header border-0 pb-0">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                    <div>
                        <h3 class="mb-1 font-weight-bold">
                            <i class="fas fa-chart-line text-primary"></i> Centro de Reportes de Rutas
                        </h3>
                        <p class="text-muted mb-0">Consulta gastos, revisa detalle operativo y genera reportes PDF por ruta.</p>
                    </div>
                    <div class="mt-3 mt-lg-0">
                        <a href="/rutas" class="btn btn-outline-primary">
                            <i class="fas fa-route"></i> Ir a rutas
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-4" id="kpiContainer">
                    <div class="col-md-6 col-xl-3 mb-3">
                        <div class="small-box bg-primary shadow-sm">
                            <div class="inner">
                                <h3 id="kpi_total_rutas">0</h3>
                                <p>Rutas analizadas</p>
                            </div>
                            <div class="icon"><i class="fas fa-road"></i></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <div class="small-box bg-info shadow-sm">
                            <div class="inner">
                                <h3 id="kpi_total_viaticos">S/ 0.00</h3>
                                <p>Total viaticos</p>
                            </div>
                            <div class="icon"><i class="fas fa-wallet"></i></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <div class="small-box bg-success shadow-sm">
                            <div class="inner">
                                <h3 id="kpi_total_combustible">S/ 0.00</h3>
                                <p>Total combustible</p>
                            </div>
                            <div class="icon"><i class="fas fa-gas-pump"></i></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <div class="small-box bg-warning shadow-sm">
                            <div class="inner">
                                <h3 id="kpi_total_gastos">S/ 0.00</h3>
                                <p>Gasto total</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <label class="mb-1">Buscar</label>
                                <input type="text"
                                    id="searchText"
                                    class="form-control"
                                    placeholder="Origen, destino, conductor o placa...">
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label class="mb-1">Fecha inicio</label>
                                <input type="date" id="fechaInicio" class="form-control">
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label class="mb-1">Fecha fin</label>
                                <input type="date" id="fechaFin" class="form-control">
                            </div>
                            <div class="col-lg-2 mb-3 d-flex align-items-end">
                                <button class="btn btn-outline-secondary btn-block" onclick="limpiarFiltrosReporte()">
                                    <i class="fas fa-eraser"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped text-center">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Ruta</th>
                                <th>Conductor</th>
                                <th>Unidad</th>
                                <th>Fechas</th>
                                <th>Viaticos</th>
                                <th>Combustible</th>
                                <th>Peajes</th>
                                <th>Total gastos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="reporteRutaTableBody"></tbody>
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
let reporteSearch = '';
let reporteFechaInicio = '';
let reporteFechaFin = '';
let debounceReporte;
let paginaReporteActual = 1;

function formatMoney(value) {
    return `S/ ${Number(value || 0).toFixed(2)}`;
}

function buildReporteQuery(page = 1) {
    const params = new URLSearchParams({
        page,
        search: reporteSearch,
        fecha_inicio: reporteFechaInicio,
        fecha_fin: reporteFechaFin
    });

    return `/api/reportes/rutas?${params.toString()}`;
}

function renderReportePagination(pagination = {}) {
    let html = '';

    for (let i = 1; i <= (pagination.last_page || 1); i++) {
        html += `
            <button class="btn btn-sm ${i === pagination.current_page ? 'btn-primary' : 'btn-light'}"
                onclick="fetchReporteRutas(${i})">
                ${i}
            </button>
        `;
    }

    $('#paginacion').html(html);
}

function renderReporteKPIs(kpis = {}) {
    $('#kpi_total_rutas').text(kpis.total_rutas || 0);
    $('#kpi_total_viaticos').text(formatMoney(kpis.total_viaticos));
    $('#kpi_total_combustible').text(formatMoney(kpis.total_combustible));
    $('#kpi_total_gastos').text(formatMoney(kpis.total_gastos));
}

function fetchReporteRutas(page = 1) {
    paginaReporteActual = page;

    apiFetch(buildReporteQuery(page))
        .then(resp => {
            const tbody = $('#reporteRutaTableBody');
            tbody.empty();

            renderReporteKPIs(resp.kpis || {});

            if (!resp.data.length) {
                tbody.html(`
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            No se encontraron rutas para el reporte.
                        </td>
                    </tr>
                `);
                $('#paginacion').html('');
                return;
            }

            resp.data.forEach(ruta => {
                tbody.append(`
                    <tr>
                        <td class="text-left">
                            <strong>${ruta.origen}</strong>
                            <small class="d-block text-muted">Destino: ${ruta.destino}</small>
                        </td>
                        <td>${ruta.conductor || '-'}</td>
                        <td>
                            <strong>${ruta.unidad?.tracto || '-'}</strong>
                            <small class="d-block text-muted">${ruta.unidad?.trailer || '-'}</small>
                        </td>
                        <td>
                            <small class="d-block">Inicio: ${ruta.fecha_inicio || '-'}</small>
                            <small class="d-block">Fin: ${ruta.fecha_fin || '-'}</small>
                        </td>
                        <td>${formatMoney(ruta.totales?.viaticos)}</td>
                        <td>${formatMoney(ruta.totales?.combustible)}</td>
                        <td>${formatMoney(ruta.totales?.peajes)}</td>
                        <td><strong>${formatMoney(ruta.totales?.gastos)}</strong></td>
                        <td>
                            <a href="/reportes/rutas/${ruta.id}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-danger btn-sm" onclick="window.open('/reportes/rutas/${ruta.id}/pdf', '_blank')">
                                <i class="fas fa-file-pdf"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            renderReportePagination(resp.pagination);
        })
        .catch(err => {
            Swal.fire('Error', err.message || 'No se pudo cargar el reporte', 'error');
        });
}

function limpiarFiltrosReporte() {
    reporteSearch = '';
    reporteFechaInicio = '';
    reporteFechaFin = '';
    $('#searchText').val('');
    $('#fechaInicio').val('');
    $('#fechaFin').val('');
    fetchReporteRutas(1);
}

$('#searchText').on('input', function () {
    const texto = $(this).val().trim();

    clearTimeout(debounceReporte);
    debounceReporte = setTimeout(() => {
        reporteSearch = texto.length >= 2 ? texto : '';
        fetchReporteRutas(1);
    }, 300);
});

$('#fechaInicio, #fechaFin').on('change', function () {
    reporteFechaInicio = $('#fechaInicio').val();
    reporteFechaFin = $('#fechaFin').val();
    fetchReporteRutas(1);
});

$(document).ready(() => {
    fetchReporteRutas();
});
</script>
@endpush
