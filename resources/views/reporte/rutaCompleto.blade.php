@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header border-0">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                    <div>
                        <h3 class="mb-1 font-weight-bold" id="tituloRutaDetalle">
                            <i class="fas fa-file-alt text-primary"></i> Reporte de Ruta
                        </h3>
                        <p class="text-muted mb-0" id="subtituloRutaDetalle">Cargando detalle operativo...</p>
                    </div>
                    <div class="mt-3 mt-lg-0">
                        <a href="/reporte-ruta" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button class="btn btn-danger" id="btnPdfRuta">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6 col-xl-3 mb-3">
                        <div class="small-box bg-primary shadow-sm">
                            <div class="inner">
                                <h3 id="detalle_total_viaticos">S/ 0.00</h3>
                                <p>Viaticos</p>
                            </div>
                            <div class="icon"><i class="fas fa-wallet"></i></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <div class="small-box bg-success shadow-sm">
                            <div class="inner">
                                <h3 id="detalle_total_combustible">S/ 0.00</h3>
                                <p>Combustible</p>
                            </div>
                            <div class="icon"><i class="fas fa-gas-pump"></i></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <div class="small-box bg-warning shadow-sm">
                            <div class="inner">
                                <h3 id="detalle_total_peajes">S/ 0.00</h3>
                                <p>Peajes</p>
                            </div>
                            <div class="icon"><i class="fas fa-road"></i></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <div class="small-box bg-info shadow-sm">
                            <div class="inner">
                                <h3 id="detalle_total_gastos">S/ 0.00</h3>
                                <p>Total gastos</p>
                            </div>
                            <div class="icon"><i class="fas fa-calculator"></i></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-5 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="font-weight-bold mb-3">Resumen operativo</h5>
                                <div id="resumenRutaDetalle" class="text-muted">Cargando...</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="font-weight-bold mb-3">Observaciones y valores</h5>
                                <div id="observacionesRutaDetalle" class="text-muted">Cargando...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" id="viaticos">
                    <div class="card-header bg-light">
                        <strong>Viaticos</strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Fecha</th>
                                        <th>Factura</th>
                                        <th>Importe</th>
                                        <th>Descripcion</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaViaticosDetalle"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" id="combustible">
                    <div class="card-header bg-light">
                        <strong>Combustible</strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Factura</th>
                                        <th>Grifo</th>
                                        <th>Fecha</th>
                                        <th>Galones</th>
                                        <th>Importe</th>
                                        <th>Km inicial</th>
                                        <th>Km final</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaCombustibleDetalle"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" id="peajes">
                    <div class="card-header bg-light">
                        <strong>Peajes</strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Fecha</th>
                                        <th>Comprobante</th>
                                        <th>Importe</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaPeajesDetalle"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const rutaReporteId = @json($id ?? request()->route('id'));

function money(value) {
    return `S/ ${Number(value || 0).toFixed(2)}`;
}

function fillOrEmpty(target, rowsHtml, colspan, emptyText) {
    if (rowsHtml.length) {
        $(target).html(rowsHtml.join(''));
        return;
    }

    $(target).html(`
        <tr>
            <td colspan="${colspan}" class="text-center text-muted">${emptyText}</td>
        </tr>
    `);
}

function cargarReporteRuta() {
    apiFetch(`/api/reportes/rutas/${rutaReporteId}`)
        .then(resp => {
            const ruta = resp.data;

            $('#tituloRutaDetalle').html(`<i class="fas fa-file-alt text-primary"></i> Reporte de Ruta #${ruta.id}`);
            $('#subtituloRutaDetalle').text(`${ruta.origen} -> ${ruta.destino}`);
            $('#btnPdfRuta').off('click').on('click', () => {
                window.open(`/reportes/rutas/${ruta.id}/pdf`, '_blank');
            });

            $('#detalle_total_viaticos').text(money(ruta.totales?.viaticos));
            $('#detalle_total_combustible').text(money(ruta.totales?.combustible));
            $('#detalle_total_peajes').text(money(ruta.totales?.peajes));
            $('#detalle_total_gastos').text(money(ruta.totales?.gastos));

            $('#resumenRutaDetalle').html(`
                <p class="mb-2"><strong>Conductor:</strong> ${ruta.conductor || '-'}</p>
                <p class="mb-2"><strong>Tracto:</strong> ${ruta.unidad?.tracto || '-'}</p>
                <p class="mb-2"><strong>Trailer:</strong> ${ruta.unidad?.trailer || '-'}</p>
                <p class="mb-2"><strong>Estado:</strong> ${ruta.estado || '-'}</p>
                <p class="mb-2"><strong>Inicio:</strong> ${ruta.fecha_inicio || '-'}</p>
                <p class="mb-0"><strong>Fin:</strong> ${ruta.fecha_fin || '-'}</p>
            `);

            $('#observacionesRutaDetalle').html(`
                <p class="mb-2"><strong>Caja chica:</strong> ${money(ruta.caja_chica)}</p>
                <p class="mb-2"><strong>Pago del viaje:</strong> ${money(ruta.pago_viaje)}</p>
                <p class="mb-2"><strong>Ganancia del viaje:</strong> ${money(ruta.ganancia_viaje)}</p>
                <p class="mb-0"><strong>Observaciones:</strong><br>${ruta.observaciones || 'Sin observaciones registradas.'}</p>
            `);

            fillOrEmpty('#tablaViaticosDetalle', (ruta.viaticos || []).map(viatico => `
                <tr>
                    <td>${viatico.nombre_servicio || '-'}</td>
                    <td>${viatico.fecha || '-'}</td>
                    <td>${viatico.numero_factura || '-'}</td>
                    <td>${money(viatico.importe)}</td>
                    <td>${viatico.descripcion || '-'}</td>
                </tr>
            `), 5, 'No hay viaticos registrados.');

            fillOrEmpty('#tablaCombustibleDetalle', (ruta.combustibles || []).map(combustible => `
                <tr>
                    <td>${combustible.num_factura || '-'}</td>
                    <td>${combustible.grifo || '-'}</td>
                    <td>${combustible.fecha_hora || '-'}</td>
                    <td>${combustible.galones || 0}</td>
                    <td>${money(combustible.importe)}</td>
                    <td>${combustible.kilometraje_inicial || '-'}</td>
                    <td>${combustible.kilometraje_final || '-'}</td>
                    <td>${combustible.tipo_combustible || '-'}</td>
                </tr>
            `), 8, 'No hay consumos de combustible registrados.');

            fillOrEmpty('#tablaPeajesDetalle', (ruta.peajes || []).map(peaje => `
                <tr>
                    <td>${peaje.nombre || '-'}</td>
                    <td>${peaje.fecha_hora || '-'}</td>
                    <td>${peaje.comprobante || '-'}</td>
                    <td>${money(peaje.importe)}</td>
                </tr>
            `), 4, 'No hay peajes registrados.');
        })
        .catch(err => {
            Swal.fire('Error', err.message || 'No se pudo cargar el detalle del reporte', 'error');
        });
}

$(document).ready(() => {
    cargarReporteRuta();
});
</script>
@endpush
