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
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <h3 class="module-title">Reporte de Ventas</h3>
                                <p class="module-subtitle">Analiza comprobantes por fecha, estado y tipo de documento.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="module-body">
                    <div class="row mb-3">
                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Desde</label>
                            <input type="date" id="filtroDesde" class="form-control">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Hasta</label>
                            <input type="date" id="filtroHasta" class="form-control">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Estado</label>
                            <select id="filtroEstado" class="form-control">
                                <option value="">Todos</option>
                                <option value="aceptado">Aceptado</option>
                                <option value="rechazado">Rechazado</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="error">Error</option>
                                <option value="procesando">Procesando</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Documento</label>
                            <select id="filtroTipoDocumento" class="form-control">
                                <option value="">Todos</option>
                                <option value="01">Factura</option>
                                <option value="03">Boleta</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Buscar</label>
                            <input type="text" id="filtroSearch" class="form-control" placeholder="Comprobante o cliente">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button type="button" class="btn btn-primary mr-2 mb-2" onclick="cargarReporteVentas(1)">
                            <i class="fas fa-search"></i> Aplicar filtros
                        </button>
                        <button type="button" class="btn btn-outline-secondary mb-2" onclick="limpiarFiltrosReporte()">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>

                    <div class="row" id="cardsResumen">
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <small class="text-muted">Comprobantes</small>
                                    <h4 class="mb-0" id="resTotalComprobantes">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <small class="text-muted">Aceptados</small>
                                    <h4 class="mb-0 text-success" id="resAceptados">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <small class="text-muted">Total PEN</small>
                                    <h4 class="mb-0" id="resTotalPen">S/ 0.00</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <small class="text-muted">Total USD</small>
                                    <h4 class="mb-0" id="resTotalUsd">US$ 0.00</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="chipsEstado"></div>

                    <div class="module-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover module-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Comprobante</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Documento</th>
                                        <th>Importe</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaReporteVentas">
                                    <tr>
                                        <td colspan="8" class="module-empty">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="paginacionReporteVentas" class="module-pagination mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let pageActualReporteVentas = 1;

function getFiltrosReporteVentas() {
    return {
        fecha_desde: document.getElementById('filtroDesde').value,
        fecha_hasta: document.getElementById('filtroHasta').value,
        estado: document.getElementById('filtroEstado').value,
        tipo_documento: document.getElementById('filtroTipoDocumento').value,
        search: document.getElementById('filtroSearch').value.trim(),
        per_page: 12
    };
}

function money(moneda, valor) {
    const amount = Number(valor || 0).toFixed(2);
    return moneda === 'USD' ? `US$ ${amount}` : `S/ ${amount}`;
}

function badgeEstado(estado) {
    if (estado === 'aceptado') return '<span class="badge badge-success">Aceptado</span>';
    if (estado === 'rechazado') return '<span class="badge badge-danger">Rechazado</span>';
    if (estado === 'error') return '<span class="badge badge-dark">Error</span>';
    if (estado === 'procesando') return '<span class="badge badge-info">Procesando</span>';
    return '<span class="badge badge-warning">Pendiente</span>';
}

function formatDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString('es-PE');
}

function updateResumen(resumen = {}) {
    document.getElementById('resTotalComprobantes').innerText = Number(resumen.total_comprobantes || 0);
    document.getElementById('resAceptados').innerText = Number(resumen.aceptados || 0);
    document.getElementById('resTotalPen').innerText = money('PEN', resumen.total_pen || 0);
    document.getElementById('resTotalUsd').innerText = money('USD', resumen.total_usd || 0);
}

function updateChipsEstado(porEstado = []) {
    const target = document.getElementById('chipsEstado');

    if (!porEstado.length) {
        target.innerHTML = '';
        return;
    }

    target.innerHTML = porEstado.map((row) => {
        const estado = row.estado_envio || 'pendiente';
        const cantidad = Number(row.cantidad || 0);
        const total = Number(row.total || 0).toFixed(2);

        return `<span class="badge badge-light mr-2 mb-2 p-2 border">
            ${estado.toUpperCase()}: ${cantidad} (${total})
        </span>`;
    }).join('');
}

function renderPaginacionReporte(ventas) {
    const wrap = document.getElementById('paginacionReporteVentas');

    if (!ventas || !ventas.last_page || ventas.last_page <= 1) {
        wrap.innerHTML = '';
        return;
    }

    let html = '';
    for (let i = 1; i <= ventas.last_page; i++) {
        html += `
            <button type="button" class="btn btn-sm ${i === ventas.current_page ? 'btn-primary' : 'btn-light'}" onclick="cargarReporteVentas(${i})">
                ${i}
            </button>
        `;
    }

    wrap.innerHTML = html;
}

function cargarReporteVentas(page = 1) {
    pageActualReporteVentas = page;
    const filtros = getFiltrosReporteVentas();
    const params = new URLSearchParams({ page, ...filtros });

    const tbody = document.getElementById('tablaReporteVentas');
    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="module-empty">
                <div class="spinner-border text-primary"></div>
            </td>
        </tr>
    `;

    apiFetch(`/api/reportes/ventas?${params.toString()}`)
        .then((resp) => {
            updateResumen(resp.resumen || {});
            updateChipsEstado(resp.por_estado || []);

            const ventas = resp.ventas || {};
            const rows = ventas.data || [];

            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="module-empty">No hay ventas para este filtro</td></tr>`;
                renderPaginacionReporte(ventas);
                return;
            }

            tbody.innerHTML = rows.map((venta) => `
                <tr>
                    <td>${venta.id}</td>
                    <td><strong>${venta.numero_comprobante || '-'}</strong></td>
                    <td>${venta.tipo_documento === '01' ? 'Factura' : (venta.tipo_documento === '03' ? 'Boleta' : venta.tipo_documento)}</td>
                    <td>${formatDate(venta.fecha_emision)}</td>
                    <td>${venta.nombre_cliente || '-'}</td>
                    <td>${venta.numero_documento_cliente || '-'}</td>
                    <td><strong>${money(venta.moneda, venta.total_venta)}</strong></td>
                    <td>${badgeEstado(venta.estado_envio)}</td>
                </tr>
            `).join('');

            renderPaginacionReporte(ventas);
        })
        .catch((err) => {
            tbody.innerHTML = `<tr><td colspan="8" class="module-empty text-danger">No se pudo cargar el reporte</td></tr>`;
            Swal.fire('Error', err.message || 'No se pudo cargar el reporte de ventas', 'error');
        });
}

function limpiarFiltrosReporte() {
    document.getElementById('filtroDesde').value = '';
    document.getElementById('filtroHasta').value = '';
    document.getElementById('filtroEstado').value = '';
    document.getElementById('filtroTipoDocumento').value = '';
    document.getElementById('filtroSearch').value = '';
    cargarReporteVentas(1);
}

document.addEventListener('DOMContentLoaded', function () {
    const hoy = new Date();
    const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    document.getElementById('filtroDesde').value = inicioMes.toISOString().slice(0, 10);
    document.getElementById('filtroHasta').value = hoy.toISOString().slice(0, 10);

    document.getElementById('filtroSearch').addEventListener('keyup', function (e) {
        if (e.key === 'Enter') {
            cargarReporteVentas(1);
        }
    });

    cargarReporteVentas(1);
});
</script>
@endpush
