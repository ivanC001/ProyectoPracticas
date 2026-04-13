@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="module-shell">
            <div class="card module-card mb-4">
                <div class="module-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="module-heading">
                            <div class="module-icon"><i class="fas fa-road"></i></div>
                            <div>
                                <h3 class="module-title">Peajes por ruta</h3>
                                <p class="module-subtitle">Registra y revisa peajes asociados a una ruta para completar el costo real del viaje.</p>
                            </div>
                        </div>
                        <div class="module-header-actions">
                            <a href="/rutas" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver a rutas</a>
                            <button type="button" class="btn btn-success" id="btn-nuevo-peaje"><i class="fas fa-plus-circle"></i> Nuevo peaje</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card module-card mb-4">
                        <div class="module-header">
                            <div class="module-heading">
                                <div class="module-icon"><i class="fas fa-route"></i></div>
                                <div>
                                    <h3 class="module-title">Informacion de la ruta</h3>
                                    <p class="module-subtitle">Contexto del viaje vinculado a estos peajes.</p>
                                </div>
                            </div>
                        </div>
                        <div class="module-body" id="ruta-info">
                            <p class="module-empty mb-0">Cargando datos...</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card module-card mb-4">
                        <div class="module-header">
                            <div class="module-heading">
                                <div class="module-icon"><i class="fas fa-chart-pie"></i></div>
                                <div>
                                    <h3 class="module-title">Resumen</h3>
                                    <p class="module-subtitle">Totales rapidos del gasto en peajes.</p>
                                </div>
                            </div>
                        </div>
                        <div class="module-body" id="peaje-resumen">
                            <p class="module-empty mb-0">Cargando resumen...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card module-card">
                <div class="module-header">
                    <div class="module-heading">
                        <div class="module-icon"><i class="fas fa-receipt"></i></div>
                        <div>
                            <h3 class="module-title">Peajes registrados</h3>
                            <p class="module-subtitle">Todos los peajes se guardan ligados a la ruta actual para tus analisis y reportes.</p>
                        </div>
                    </div>
                </div>

                <div class="module-body">
                    <div class="module-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover module-table text-center">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Importe</th>
                                        <th>Fecha y hora</th>
                                        <th>Comprobante</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="peaje-list">
                                    <tr>
                                        <td colspan="6" class="module-empty">Cargando registros de peajes...</td>
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

<div class="modal fade" id="peajeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="peajeForm">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="peajeModalTitle">Registrar peaje</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="peajeId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre</label>
                            <input type="text" id="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Importe</label>
                            <input type="number" step="0.01" id="importe" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Fecha y hora</label>
                            <input type="datetime-local" id="fecha_hora" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Comprobante</label>
                            <input type="text" id="comprobante" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="peajeSubmitBtn" class="btn btn-success">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const rutaId = window.location.pathname.split('/')[2];
const apiPeajeUrl = `/api/rutas/${rutaId}/peajes`;

function formatMoney(value) {
    return `S/ ${Number(value || 0).toFixed(2)}`;
}

function getDateTimeInputValue(value) {
    if (!value) {
        return '';
    }

    return String(value).slice(0, 16);
}

async function fetchRuta() {
    try {
        const response = await apiFetch(`/api/rutas/${rutaId}`);
        const ruta = response.data || response;

        $('#ruta-info').html(`
            <div class="row">
                <div class="col-md-6 mb-3"><strong>Origen:</strong><br>${ruta.origen || '-'}</div>
                <div class="col-md-6 mb-3"><strong>Destino:</strong><br>${ruta.destino || '-'}</div>
                <div class="col-md-6 mb-3"><strong>Conductor:</strong><br>${ruta.conductor?.nombre || '-'} ${ruta.conductor?.apellido || ''}</div>
                <div class="col-md-6 mb-3"><strong>Unidad:</strong><br>${ruta.camion?.placa_tracto || '-'} / ${ruta.camion?.placa_carreto || '-'}</div>
                <div class="col-md-6"><strong>Salida:</strong><br>${ruta.fecha_inicio || '-'}</div>
                <div class="col-md-6"><strong>Llegada:</strong><br>${ruta.fecha_fin || '-'}</div>
            </div>
        `);
    } catch (error) {
        $('#ruta-info').html('<p class="module-empty text-danger mb-0">No se pudo cargar la informacion de la ruta.</p>');
    }
}

function renderPeajeResumen(rows) {
    const total = rows.reduce((sum, item) => sum + Number(item.importe || 0), 0);
    const ultimo = rows.length ? rows[0].fecha_hora || '-' : '-';

    $('#peaje-resumen').html(`
        <div class="mb-3">
            <small class="text-muted d-block">Registros</small>
            <strong>${rows.length}</strong>
        </div>
        <div class="mb-3">
            <small class="text-muted d-block">Monto acumulado</small>
            <strong>${formatMoney(total)}</strong>
        </div>
        <div>
            <small class="text-muted d-block">Ultimo movimiento</small>
            <strong>${ultimo}</strong>
        </div>
    `);
}

async function fetchPeajes() {
    try {
        const response = await apiFetch(apiPeajeUrl);
        const rows = Array.isArray(response) ? response : [];
        renderPeajeResumen(rows);

        if (!rows.length) {
            $('#peaje-list').html('<tr><td colspan="6" class="module-empty">No hay registros de peajes para esta ruta.</td></tr>');
            return;
        }

        let html = '';

        rows.forEach((peaje, index) => {
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td class="text-left">${peaje.nombre || '-'}</td>
                    <td>${formatMoney(peaje.importe)}</td>
                    <td>${peaje.fecha_hora || '-'}</td>
                    <td>${peaje.comprobante || '-'}</td>
                    <td>
                        <div class="table-action-group">
                            <button type="button" class="btn btn-soft-warning btn-sm" onclick="editPeaje(${peaje.id})">Editar</button>
                            <button type="button" class="btn btn-soft-danger btn-sm" onclick="deletePeaje(${peaje.id})">Eliminar</button>
                        </div>
                    </td>
                </tr>
            `;
        });

        $('#peaje-list').html(html);
    } catch (error) {
        const message = String(error.message || '').toLowerCase();

        if (message.includes('no hay registros')) {
            renderPeajeResumen([]);
            $('#peaje-list').html('<tr><td colspan="6" class="module-empty">No hay registros de peajes para esta ruta.</td></tr>');
            return;
        }

        renderPeajeResumen([]);
        $('#peaje-list').html('<tr><td colspan="6" class="module-empty text-danger">Error al cargar peajes.</td></tr>');
    }
}

$('#peajeForm').on('submit', async function (e) {
    e.preventDefault();

    const submitBtn = $('#peajeSubmitBtn');
    submitBtn.prop('disabled', true).text('Guardando...');

    const id = $('#peajeId').val();
    const payload = {
        nombre: $('#nombre').val(),
        importe: $('#importe').val(),
        fecha_hora: $('#fecha_hora').val(),
        comprobante: $('#comprobante').val()
    };

    try {
        await apiFetch(id ? `${apiPeajeUrl}/${id}` : apiPeajeUrl, {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(payload)
        });

        $('#peajeModal').modal('hide');
        Swal.fire('OK', 'Peaje guardado correctamente.', 'success');
        fetchPeajes();
    } catch (error) {
        Swal.fire('Error', error.message || 'No se pudo guardar el peaje.', 'error');
    } finally {
        submitBtn.prop('disabled', false).text('Guardar');
    }
});

async function editPeaje(id) {
    try {
        const peaje = await apiFetch(`${apiPeajeUrl}/${id}`);
        $('#peajeModalTitle').text('Editar peaje');
        $('#peajeId').val(peaje.id);
        $('#nombre').val(peaje.nombre);
        $('#importe').val(peaje.importe);
        $('#fecha_hora').val(getDateTimeInputValue(peaje.fecha_hora));
        $('#comprobante').val(peaje.comprobante);
        $('#peajeSubmitBtn').prop('disabled', false).text('Actualizar');
        $('#peajeModal').modal('show');
    } catch (error) {
        Swal.fire('Error', error.message || 'No se pudo cargar el peaje.', 'error');
    }
}

async function deletePeaje(id) {
    const result = await Swal.fire({
        title: 'Eliminar peaje?',
        text: 'Esta accion no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar'
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        await apiFetch(`${apiPeajeUrl}/${id}`, {
            method: 'DELETE'
        });

        Swal.fire('OK', 'Peaje eliminado correctamente.', 'success');
        fetchPeajes();
    } catch (error) {
        Swal.fire('Error', error.message || 'No se pudo eliminar el peaje.', 'error');
    }
}

$('#btn-nuevo-peaje').on('click', function () {
    $('#peajeForm')[0].reset();
    $('#peajeId').val('');
    $('#peajeModalTitle').text('Registrar peaje');
    $('#fecha_hora').val(new Date().toISOString().slice(0, 16));
    $('#peajeSubmitBtn').prop('disabled', false).text('Guardar');
    $('#peajeModal').modal('show');
});

$(document).ready(() => {
    fetchRuta();
    fetchPeajes();
});
</script>
@endpush
