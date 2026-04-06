<div class="modal fade" id="modalSegurosCamion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="mb-0" id="tituloModalSeguros">
                    <i class="fas fa-shield-alt"></i> Seguros de la unidad
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Seguro</th>
                                        <th>Poliza</th>
                                        <th>Vence</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="segurosTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Sin seguros registrados</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="border rounded p-3 bg-light">
                            <h6 class="font-weight-bold" id="tituloFormSeguro">Registrar seguro</h6>
                            <form id="formSeguroCamion">
                                <div class="form-group">
                                    <label for="seguro_tipo">Tipo de seguro *</label>
                                    <input type="text" class="form-control" id="seguro_tipo" placeholder="SOAT, vehicular, carga...">
                                </div>
                                <div class="form-group">
                                    <label for="seguro_aseguradora">Aseguradora</label>
                                    <input type="text" class="form-control" id="seguro_aseguradora">
                                </div>
                                <div class="form-group">
                                    <label for="seguro_poliza">Numero de poliza</label>
                                    <input type="text" class="form-control" id="seguro_poliza">
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="seguro_inicio">Fecha inicio</label>
                                        <input type="date" class="form-control" id="seguro_inicio">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="seguro_vencimiento">Fecha vencimiento *</label>
                                        <input type="date" class="form-control" id="seguro_vencimiento">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="seguro_monto">Monto</label>
                                        <input type="number" step="0.01" class="form-control" id="seguro_monto">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="seguro_alerta">Alertar dias antes</label>
                                        <input type="number" min="1" max="365" class="form-control" id="seguro_alerta" value="30">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="seguro_observaciones">Observaciones</label>
                                    <textarea class="form-control" id="seguro_observaciones" rows="3"></textarea>
                                </div>
                                <div class="form-group mb-2">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="seguro_activo" checked>
                                        <label class="custom-control-label" for="seguro_activo">Seguro activo</label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary mr-2" id="btnCancelarSeguro">Limpiar</button>
                                    <button type="button" class="btn btn-info" id="btnGuardarSeguro">Guardar seguro</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let camionSeguroActualId = null;
let seguroEditandoId = null;

function seguroEstadoBadge(seguro) {
    if (seguro.estado_alerta === 'vencido') {
        return `<span class="badge badge-danger">Vencido</span>`;
    }

    if (seguro.estado_alerta === 'por_vencer') {
        return `<span class="badge badge-warning">Por vencer</span>`;
    }

    return `<span class="badge badge-success">Vigente</span>`;
}

function resetSeguroForm() {
    seguroEditandoId = null;
    $('#tituloFormSeguro').text('Registrar seguro');
    $('#formSeguroCamion')[0].reset();
    $('#seguro_alerta').val('30');
    $('#seguro_activo').prop('checked', true);
}

function renderSegurosTable(seguros = []) {
    const tbody = $('#segurosTableBody');
    tbody.empty();

    if (!seguros.length) {
        tbody.html('<tr><td colspan="5" class="text-center text-muted">Sin seguros registrados</td></tr>');
        return;
    }

    seguros.forEach(seguro => {
        const estadoTexto = Number(seguro.dias_restantes) < 0
            ? `Vencido hace ${Math.abs(Number(seguro.dias_restantes))} dia(s)`
            : `Vence en ${seguro.dias_restantes} dia(s)`;

        tbody.append(`
            <tr>
                <td>
                    <strong>${seguro.tipo_seguro}</strong>
                    <small class="d-block text-muted">${seguro.aseguradora || '-'}</small>
                </td>
                <td>${seguro.numero_poliza || '-'}</td>
                <td>
                    ${seguro.fecha_vencimiento}
                    <small class="d-block text-muted">${estadoTexto}</small>
                </td>
                <td>${seguroEstadoBadge(seguro)}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editarSeguro(${seguro.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="eliminarSeguro(${seguro.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function cargarSeguros() {
    if (!camionSeguroActualId) {
        return;
    }

    apiFetch(`/api/camiones/${camionSeguroActualId}/seguros`)
        .then(resp => {
            window.segurosUnidadActual = resp.data || [];
            renderSegurosTable(window.segurosUnidadActual);
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudieron cargar los seguros', 'error');
        });
}

function abrirSeguros(camionId, placaTracto, placaTrailer) {
    camionSeguroActualId = camionId;
    $('#tituloModalSeguros').html(`<i class="fas fa-shield-alt"></i> Seguros de ${placaTracto} / ${placaTrailer}`);
    resetSeguroForm();
    cargarSeguros();
    $('#modalSegurosCamion').modal('show');
}

function editarSeguro(seguroId) {
    const seguro = (window.segurosUnidadActual || []).find(item => Number(item.id) === Number(seguroId));

    if (!seguro) {
        return;
    }

    seguroEditandoId = seguroId;
    $('#tituloFormSeguro').text('Editar seguro');
    $('#seguro_tipo').val(seguro.tipo_seguro || '');
    $('#seguro_aseguradora').val(seguro.aseguradora || '');
    $('#seguro_poliza').val(seguro.numero_poliza || '');
    $('#seguro_inicio').val(seguro.fecha_inicio || '');
    $('#seguro_vencimiento').val(seguro.fecha_vencimiento || '');
    $('#seguro_monto').val(seguro.monto || '');
    $('#seguro_alerta').val(seguro.alertar_dias_antes || 30);
    $('#seguro_observaciones').val(seguro.observaciones || '');
    $('#seguro_activo').prop('checked', !!seguro.activo);
}

function eliminarSeguro(seguroId) {
    Swal.fire({
        title: '¿Eliminar seguro?',
        icon: 'warning',
        showCancelButton: true
    }).then(result => {
        if (!result.isConfirmed) {
            return;
        }

        apiFetch(`/api/camiones/${camionSeguroActualId}/seguros/${seguroId}`, {
            method: 'DELETE'
        }).then(resp => {
            Swal.fire('OK', resp.message, 'success');
            cargarSeguros();
            fetchCamiones(paginaCamionActual);
            resetSeguroForm();
        });
    });
}

$('#btnGuardarSeguro').on('click', function () {
    if (!camionSeguroActualId) {
        return;
    }

    const payload = {
        tipo_seguro: $('#seguro_tipo').val().trim(),
        aseguradora: $('#seguro_aseguradora').val().trim(),
        numero_poliza: $('#seguro_poliza').val().trim(),
        fecha_inicio: $('#seguro_inicio').val() || null,
        fecha_vencimiento: $('#seguro_vencimiento').val(),
        monto: $('#seguro_monto').val() || null,
        alertar_dias_antes: $('#seguro_alerta').val() || 30,
        activo: $('#seguro_activo').is(':checked'),
        observaciones: $('#seguro_observaciones').val().trim()
    };

    const url = seguroEditandoId
        ? `/api/camiones/${camionSeguroActualId}/seguros/${seguroEditandoId}`
        : `/api/camiones/${camionSeguroActualId}/seguros`;
    const method = seguroEditandoId ? 'PUT' : 'POST';

    apiFetch(url, {
        method,
        body: JSON.stringify(payload)
    }).then(resp => {
        Swal.fire('OK', resp.message, 'success');
        cargarSeguros();
        fetchCamiones(paginaCamionActual);
        resetSeguroForm();
    }).catch(err => {
        const message = err.errors
            ? Object.values(err.errors).flat()[0]
            : (err.message || 'No se pudo guardar el seguro');
        Swal.fire('Error', message, 'error');
    });
});

$('#btnCancelarSeguro').on('click', resetSeguroForm);

$('#modalSegurosCamion').on('hidden.bs.modal', function () {
    camionSeguroActualId = null;
    window.segurosUnidadActual = [];
    resetSeguroForm();
});
</script>
@endpush
