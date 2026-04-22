@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="module-shell">
            <div class="card module-card mb-4">
                <div class="module-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="module-heading">
                            <div class="module-icon"><i class="fas fa-gas-pump"></i></div>
                            <div>
                                <h3 class="module-title">Combustible por ruta</h3>
                                <p class="module-subtitle">Controla abastecimientos, kilometraje y comprobantes ligados a una ruta especifica.</p>
                            </div>
                        </div>
                        <div class="module-header-actions">
                            <a href="/rutas" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver a rutas</a>
                            <button type="button" class="btn btn-success" id="btn-nuevo"><i class="fas fa-plus-circle"></i> Nuevo registro</button>
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
                                    <p class="module-subtitle">Contexto del viaje asociado a los consumos de combustible.</p>
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
                                    <p class="module-subtitle">Lectura rapida del consumo actual.</p>
                                </div>
                            </div>
                        </div>
                        <div class="module-body" id="combustible-resumen">
                            <p class="module-empty mb-0">Cargando resumen...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card module-card">
                <div class="module-header">
                    <div class="module-heading">
                        <div class="module-icon"><i class="fas fa-oil-can"></i></div>
                        <div>
                            <h3 class="module-title">Registros de combustible</h3>
                            <p class="module-subtitle">Cada carga queda vinculada a la ruta actual para un mejor control de costos.</p>
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
                                        <th>Factura</th>
                                        <th>Grifo</th>
                                        <th>Fecha y hora</th>
                                        <th>Galones</th>
                                        <th>Importe</th>
                                        <th>Km inicial</th>
                                        <th>Km final</th>
                                        <th>Tipo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="combustible-list">
                                    <tr><td colspan="10" class="module-empty">Cargando registros de combustible...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="combustibleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="combustibleForm">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="combustibleModalTitle">Registrar consumo de combustible</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="combustibleId">
          <div class="alert alert-light border mb-3">
            <strong>Campos obligatorios:</strong> Factura, Grifo, Fecha y hora, Galones, Importe, Km inicial y Tipo de combustible.
            <br>
            <small class="text-muted">El Km final es opcional y puede registrarse cuando termine el tramo.</small>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="num_factura">Numero de factura <span class="text-danger">*</span></label>
              <input type="text" id="num_factura" class="form-control" maxlength="255" required autocomplete="off" placeholder="Ej: F001-123">
            </div>
            <div class="col-md-6 mb-3">
              <label for="grifo">Grifo <span class="text-danger">*</span></label>
              <input type="text" id="grifo" class="form-control" maxlength="255" required autocomplete="off" placeholder="Ej: Repsol, Primax, Petroperu">
            </div>
            <div class="col-md-6 mb-3">
              <label for="fecha_hora">Fecha y hora <span class="text-danger">*</span></label>
              <input type="datetime-local" id="fecha_hora" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="galonesCombustible">Galones <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0.01" id="galonesCombustible" class="form-control" required placeholder="0.00">
            </div>
            <div class="col-md-6 mb-3">
              <label for="importe">Importe (S/) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0.01" id="importe" class="form-control" required placeholder="0.00">
            </div>
            <div class="col-md-6 mb-3">
              <label for="kilometraje_inicial">Kilometraje inicial <span class="text-danger">*</span></label>
              <input type="number" min="0" id="kilometraje_inicial" class="form-control" required placeholder="Ej: 145000">
            </div>
            <div class="col-md-6 mb-3">
              <label for="kilometraje_final">Kilometraje final</label>
              <input type="number" min="0" id="kilometraje_final" class="form-control" placeholder="Opcional">
            </div>
            <div class="col-md-6 mb-3">
              <label for="tipo_combustible">Tipo de combustible <span class="text-danger">*</span></label>
              <select id="tipo_combustible" class="form-control" required>
                  <option value="">Seleccione...</option>
                  <option value="Diesel">Diesel</option>
                  <option value="Gasolina">Gasolina</option>
                  <option value="GLP">GLP</option>
                  <option value="GNV">GNV</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="combustibleSubmitBtn" class="btn btn-success">Guardar</button>
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
const apiUrl = `/api/rutas/${rutaId}/combustibles`;

function formatMoney(value) {
    return `S/ ${Number(value || 0).toFixed(2)}`;
}

function getDateTimeInputValue(value) {
    if (!value) {
        return '';
    }

    return String(value).slice(0, 16);
}

function cleanTextValue(selector) {
    return String($(selector).val() || '').trim();
}

function cleanNullableNumber(selector) {
    const raw = String($(selector).val() || '').trim();
    return raw === '' ? null : raw;
}

function buildApiErrorHtml(error, fallbackMessage) {
    const validationErrors = (error && typeof error === 'object' && error.errors) ? error.errors : null;

    if (!validationErrors) {
        return `<p class="mb-0">${error?.message || fallbackMessage}</p>`;
    }

    const firstItems = Object.values(validationErrors)
        .flat()
        .filter(Boolean)
        .slice(0, 6);

    if (!firstItems.length) {
        return `<p class="mb-0">${fallbackMessage}</p>`;
    }

    return `<ul class="text-left mb-0">${firstItems.map(item => `<li>${item}</li>`).join('')}</ul>`;
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

function renderCombustibleResumen(rows) {
    const total = rows.reduce((sum, item) => sum + Number(item.importe || 0), 0);
    const galones = rows.reduce((sum, item) => sum + Number(item.galonesCombustible || 0), 0);

    $('#combustible-resumen').html(`
        <div class="mb-3">
            <small class="text-muted d-block">Registros</small>
            <strong>${rows.length}</strong>
        </div>
        <div class="mb-3">
            <small class="text-muted d-block">Galones acumulados</small>
            <strong>${galones.toFixed(2)}</strong>
        </div>
        <div>
            <small class="text-muted d-block">Costo total</small>
            <strong>${formatMoney(total)}</strong>
        </div>
    `);
}

async function fetchCombustible() {
    try {
        const response = await apiFetch(apiUrl);
        const rows = Array.isArray(response) ? response : [];
        renderCombustibleResumen(rows);

        let html = '';

        if (rows.length) {
            rows.forEach((c, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${c.num_factura || '-'}</td>
                        <td class="text-left">${c.grifo || '-'}</td>
                        <td>${c.fecha_hora || '-'}</td>
                        <td>${c.galonesCombustible || '-'}</td>
                        <td>${formatMoney(c.importe)}</td>
                        <td>${c.kilometraje_inicial || '-'}</td>
                        <td>${c.kilometraje_final || '-'}</td>
                        <td>${c.tipo_combustible || '-'}</td>
                        <td>
                            <div class="table-action-group">
                                <button type="button" class="btn btn-soft-warning btn-sm" onclick="editCombustible(${c.id})">Editar</button>
                                <button type="button" class="btn btn-soft-danger btn-sm" onclick="deleteCombustible(${c.id})">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="10" class="module-empty">No hay registros de combustible para esta ruta.</td></tr>';
        }

        $('#combustible-list').html(html);
    } catch (error) {
        $('#combustible-list').html('<tr><td colspan="10" class="module-empty text-danger">Error al cargar combustibles.</td></tr>');
        $('#combustible-resumen').html('<p class="module-empty text-danger mb-0">No se pudo cargar el resumen.</p>');
    }
}

$('#combustibleForm').on('submit', async function (e) {
    e.preventDefault();

    const submitBtn = $('#combustibleSubmitBtn');
    submitBtn.prop('disabled', true).text('Guardando...');

    const id = $('#combustibleId').val();
    const payload = {
        num_factura: cleanTextValue('#num_factura'),
        grifo: cleanTextValue('#grifo'),
        fecha_hora: cleanTextValue('#fecha_hora'),
        galonesCombustible: cleanTextValue('#galonesCombustible'),
        importe: cleanTextValue('#importe'),
        kilometraje_inicial: cleanTextValue('#kilometraje_inicial'),
        kilometraje_final: cleanNullableNumber('#kilometraje_final'),
        tipo_combustible: cleanTextValue('#tipo_combustible'),
    };

    if (!payload.num_factura || !payload.grifo || !payload.fecha_hora || !payload.galonesCombustible || !payload.importe || !payload.kilometraje_inicial || !payload.tipo_combustible) {
        Swal.fire('Validacion', 'Completa todos los campos obligatorios.', 'warning');
        submitBtn.prop('disabled', false).text(id ? 'Actualizar' : 'Guardar');
        return;
    }

    try {
        await apiFetch(id ? `${apiUrl}/${id}` : apiUrl, {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(payload)
        });

        $('#combustibleModal').modal('hide');
        Swal.fire('OK', 'Registro guardado correctamente.', 'success');
        fetchCombustible();
    } catch (error) {
        Swal.fire({
            title: 'Error',
            icon: 'error',
            html: buildApiErrorHtml(error, 'No se pudo guardar el combustible.'),
        });
    } finally {
        submitBtn.prop('disabled', false).text(id ? 'Actualizar' : 'Guardar');
    }
});

async function editCombustible(id) {
    try {
        const c = await apiFetch(`${apiUrl}/${id}`);
        $('#combustibleModalTitle').text('Editar combustible');
        $('#combustibleId').val(c.id);
        $('#num_factura').val(c.num_factura);
        $('#grifo').val(c.grifo);
        $('#fecha_hora').val(getDateTimeInputValue(c.fecha_hora));
        $('#galonesCombustible').val(c.galonesCombustible);
        $('#importe').val(c.importe);
        $('#kilometraje_inicial').val(c.kilometraje_inicial);
        $('#kilometraje_final').val(c.kilometraje_final);
        $('#tipo_combustible').val(c.tipo_combustible);
        $('#combustibleSubmitBtn').prop('disabled', false).text('Actualizar');
        $('#combustibleModal').modal('show');
    } catch (error) {
        Swal.fire('Error', error.message || 'No se pudo cargar el registro.', 'error');
    }
}

async function deleteCombustible(id) {
    const result = await Swal.fire({
        title: 'Eliminar registro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar'
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        await apiFetch(`${apiUrl}/${id}`, { method: 'DELETE' });
        Swal.fire('OK', 'Registro eliminado correctamente.', 'success');
        fetchCombustible();
    } catch (error) {
        Swal.fire('Error', error.message || 'No se pudo eliminar el registro.', 'error');
    }
}

$('#btn-nuevo').click(() => {
    $('#combustibleForm')[0].reset();
    $('#combustibleId').val('');
    $('#combustibleModalTitle').text('Registrar consumo de combustible');
    $('#fecha_hora').val(new Date().toISOString().slice(0, 16));
    $('#tipo_combustible').val('');
    $('#combustibleSubmitBtn').prop('disabled', false).text('Guardar');
    $('#combustibleModal').modal('show');
});

$(document).ready(() => {
    fetchRuta();
    fetchCombustible();
});
</script>
@endpush
