@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="module-shell">
            <div class="card module-card mb-4">
                <div class="module-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="module-heading">
                            <div class="module-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div>
                                <h3 class="module-title">Viaticos por ruta</h3>
                                <p class="module-subtitle">Administra los gastos de viaje de una ruta especifica con una vista mas clara y rapida.</p>
                            </div>
                        </div>
                        <div class="module-header-actions">
                            <a href="/rutas" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver a rutas</a>
                            <button type="button" class="btn btn-success" onclick="openViaticoModal()"><i class="fas fa-plus-circle"></i> Agregar viatico</button>
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
                                    <p class="module-subtitle">Resumen operativo del viaje vinculado a estos gastos.</p>
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
                                    <p class="module-subtitle">Totales rapidos de la ruta actual.</p>
                                </div>
                            </div>
                        </div>
                        <div class="module-body" id="viatico-resumen">
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
                            <h3 class="module-title">Viaticos registrados</h3>
                            <p class="module-subtitle">Cada registro queda asociado a esta ruta para mantener trazabilidad completa.</p>
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
                                        <th>Servicio</th>
                                        <th>Fecha</th>
                                        <th>Factura</th>
                                        <th>Importe</th>
                                        <th>Descripcion</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="viaticos-list">
                                    <tr><td colspan="7" class="module-empty">Cargando viaticos...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viaticoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="viaticoModalTitle">Agregar viatico</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="viaticoForm">
                    <input type="hidden" name="id" id="viaticoId">
                    <div class="mb-3">
                        <label>Nombre del servicio</label>
                        <input type="text" name="nombre_servicio" id="viaticoNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" id="viaticoFecha" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Numero de factura</label>
                        <input type="text" name="numero_factura" id="viaticoFactura" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Importe</label>
                        <input type="number" step="0.01" name="importe" id="viaticoImporte" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Descripcion</label>
                        <textarea name="descripcion" id="viaticoDescripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" id="viaticoSubmitBtn" class="btn btn-success btn-block">Guardar viatico</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const rutaId = window.location.pathname.split('/')[2];
let currentViaticos = [];

function formatMoney(value) {
    return `S/ ${Number(value || 0).toFixed(2)}`;
}

async function fetchRuta() {
    const ruta = await apiFetch(`/api/rutasViaticos/${rutaId}`);

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

    renderViaticos(ruta.viaticos || []);
}

function renderResumen(viaticos) {
    const total = viaticos.reduce((sum, item) => sum + Number(item.importe || 0), 0);
    const ultimo = viaticos.length ? viaticos[0].fecha || '-' : '-';

    $('#viatico-resumen').html(`
        <div class="mb-3">
            <small class="text-muted d-block">Registros</small>
            <strong>${viaticos.length}</strong>
        </div>
        <div class="mb-3">
            <small class="text-muted d-block">Total acumulado</small>
            <strong>${formatMoney(total)}</strong>
        </div>
        <div>
            <small class="text-muted d-block">Ultimo movimiento</small>
            <strong>${ultimo}</strong>
        </div>
    `);
}

function renderViaticos(viaticos) {
    currentViaticos = viaticos;
    renderResumen(viaticos);

    const rows = viaticos.length
        ? viaticos.map((viatico, index) => `
            <tr>
                <td>${index + 1}</td>
                <td class="text-left">${viatico.nombre_servicio || '-'}</td>
                <td>${viatico.fecha || '-'}</td>
                <td>${viatico.numero_factura || '-'}</td>
                <td>${formatMoney(viatico.importe)}</td>
                <td class="text-left">${viatico.descripcion || '-'}</td>
                <td>
                    <div class="table-action-group">
                        <button type="button" class="btn btn-soft-warning btn-sm" onclick="editViatico(${index})">Editar</button>
                        <button type="button" class="btn btn-soft-danger btn-sm" onclick="deleteViatico(${viatico.id})">Eliminar</button>
                    </div>
                </td>
            </tr>
        `).join('')
        : '<tr><td colspan="7" class="module-empty">No hay viaticos registrados para esta ruta.</td></tr>';

    $('#viaticos-list').html(rows);
}

function openViaticoModal() {
    $('#viaticoForm')[0].reset();
    $('#viaticoId').val('');
    $('#viaticoFecha').val(new Date().toISOString().slice(0, 10));
    $('#viaticoModalTitle').text('Agregar viatico');
    $('#viaticoSubmitBtn').prop('disabled', false).text('Guardar viatico');
    $('#viaticoModal').modal('show');
}

function editViatico(index) {
    const viatico = currentViaticos[index];

    if (!viatico) {
        return;
    }

    $('#viaticoId').val(viatico.id);
    $('#viaticoNombre').val(viatico.nombre_servicio);
    $('#viaticoFecha').val(viatico.fecha);
    $('#viaticoFactura').val(viatico.numero_factura);
    $('#viaticoImporte').val(viatico.importe);
    $('#viaticoDescripcion').val(viatico.descripcion);
    $('#viaticoModalTitle').text('Editar viatico');
    $('#viaticoSubmitBtn').prop('disabled', false).text('Actualizar viatico');
    $('#viaticoModal').modal('show');
}

$('#viaticoForm').submit(async function (e) {
    e.preventDefault();

    const submitBtn = $('#viaticoSubmitBtn');
    submitBtn.prop('disabled', true).text('Guardando...');

    const viatico = {
        id: $('#viaticoId').val() || null,
        nombre_servicio: $('#viaticoNombre').val(),
        fecha: $('#viaticoFecha').val(),
        numero_factura: $('#viaticoFactura').val(),
        importe: $('#viaticoImporte').val(),
        descripcion: $('#viaticoDescripcion').val()
    };

    try {
        await apiFetch(`/api/rutasViaticos/${rutaId}`, {
            method: 'PUT',
            body: JSON.stringify({ viaticos: [viatico] })
        });

        $('#viaticoModal').modal('hide');
        Swal.fire('OK', 'Datos guardados correctamente.', 'success');
        fetchRuta();
    } catch (error) {
        Swal.fire('Error', error.message || 'No se pudo guardar el viatico.', 'error');
    } finally {
        submitBtn.prop('disabled', false).text('Guardar viatico');
    }
});

async function deleteViatico(id) {
    const result = await Swal.fire({
        title: 'Eliminar viatico?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar'
    });

    if (!result.isConfirmed) {
        return;
    }

    $.ajax({
        method: 'DELETE',
        url: `/api/viaticos/${id}`,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function () {
            Swal.fire('OK', 'Viatico eliminado correctamente.', 'success');
            fetchRuta();
        },
        error: function (xhr) {
            const message = xhr.responseJSON?.message || 'No se pudo eliminar el viatico.';
            Swal.fire('Error', message, 'error');
        }
    });
}

$(document).ready(() => {
    fetchRuta();
});
</script>
@endpush
