<div class="modal fade" id="modalRegistroRuta" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="mb-0" id="modalRegistroRutaLabel">
                    <i class="fas fa-route"></i> Registrar Ruta
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="formRegistroRuta">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="origen">Origen *</label>
                            <input type="text" class="form-control" id="origen" name="origen" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="destino">Destino *</label>
                            <input type="text" class="form-control" id="destino" name="destino" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="fecha_inicio">Fecha de salida *</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="fecha_fin">Fecha de llegada *</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="conductor">Conductor *</label>
                            <select class="form-control" id="conductor" name="conductor_id" required></select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Unidad asignada</label>
                            <div class="border rounded bg-light p-3" id="unidadAsignadaPreview">
                                Selecciona un conductor para ver su tracto y trailer.
                            </div>
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="caja_chica">Caja chica</label>
                            <input type="number" step="0.01" class="form-control" id="caja_chica" name="caja_chica">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="pago_viaje">Pago del viaje</label>
                            <input type="number" step="0.01" class="form-control" id="pago_viaje" name="pago_viaje">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="estado">Estado *</label>
                            <select class="form-control" id="estado" name="estado" required>
                                <option value="">Seleccione...</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="en curso">En curso</option>
                                <option value="finalizado">Finalizado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarRuta">
                    <i class="fas fa-save"></i> Guardar Ruta
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let editingRutaId = null;
let conductoresRuta = [];

function setDefaultDatesRuta() {
    const today = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(nextWeek.getDate() + 7);

    $('#fecha_inicio').val(today.toISOString().slice(0, 10));
    $('#fecha_fin').val(nextWeek.toISOString().slice(0, 10));
}

function renderUnidadAsignada(conductorId = '') {
    const conductor = conductoresRuta.find(item => Number(item.id) === Number(conductorId));

    if (!conductor || !conductor.camion) {
        $('#unidadAsignadaPreview').html('Selecciona un conductor para ver su tracto y trailer.');
        return;
    }

    $('#unidadAsignadaPreview').html(`
        <strong>Tracto:</strong> ${conductor.camion.placa_tracto || '-'}<br>
        <strong>Trailer:</strong> ${conductor.camion.placa_carreto || '-'}<br>
        <strong>MTC:</strong> ${conductor.camion.mtc || '-'}
    `);
}

function fetchConductoresRuta(selectedId = '') {
    return apiFetch('/api/conductores?per_page=200')
        .then(resp => {
            conductoresRuta = (resp.data || []).filter(conductor => conductor.camion);
            const conductorSelect = $('#conductor');
            conductorSelect.empty().append('<option value="">Seleccione un conductor</option>');

            conductoresRuta.forEach(conductor => {
                const nombre = `${conductor.nombre || ''} ${conductor.apellido || ''}`.trim();
                const unidad = conductor.camion
                    ? ` - ${conductor.camion.placa_tracto} / ${conductor.camion.placa_carreto}`
                    : ' - Sin unidad';
                const selected = Number(selectedId) === Number(conductor.id) ? 'selected' : '';

                conductorSelect.append(`
                    <option value="${conductor.id}" ${selected}>
                        ${nombre}${unidad}
                    </option>
                `);
            });

            renderUnidadAsignada(selectedId);
        });
}

function editar(id) {
    Promise.all([
        apiFetch(`/api/rutas/${id}`),
        fetchConductoresRuta()
    ]).then(([resp]) => {
        const ruta = resp.data;

        editingRutaId = id;
        $('#modalRegistroRutaLabel').html('<i class="fas fa-edit"></i> Editar Ruta');
        $('#origen').val(ruta.origen || '');
        $('#destino').val(ruta.destino || '');
        $('#fecha_inicio').val(ruta.fecha_inicio || '');
        $('#fecha_fin').val(ruta.fecha_fin || '');
        $('#conductor').val(ruta.conductor_id || '');
        $('#caja_chica').val(ruta.caja_chica || '');
        $('#pago_viaje').val(ruta.pago_viaje || '');
        $('#estado').val(ruta.estado || '');
        $('#observaciones').val(ruta.observaciones || '');
        renderUnidadAsignada(ruta.conductor_id || '');

        $('#modalRegistroRuta').modal('show');
    }).catch(err => {
        Swal.fire('Error', err.message || 'No se pudo cargar la ruta', 'error');
    });
}

$('#conductor').on('change', function () {
    renderUnidadAsignada($(this).val());
});

$('#btnGuardarRuta').on('click', function () {
    const payload = {
        origen: $('#origen').val().trim(),
        destino: $('#destino').val().trim(),
        fecha_inicio: $('#fecha_inicio').val(),
        fecha_fin: $('#fecha_fin').val(),
        conductor_id: $('#conductor').val(),
        caja_chica: $('#caja_chica').val() || null,
        pago_viaje: $('#pago_viaje').val() || null,
        estado: $('#estado').val(),
        observaciones: $('#observaciones').val().trim()
    };

    const url = editingRutaId ? `/api/rutas/${editingRutaId}` : '/api/rutas';
    const method = editingRutaId ? 'PUT' : 'POST';

    apiFetch(url, {
        method,
        body: JSON.stringify(payload)
    }).then(resp => {
        Swal.fire('OK', resp.message, 'success');
        $('#modalRegistroRuta').modal('hide');
        fetchRutas(paginaActual);
    }).catch(err => {
        const message = err.errors
            ? Object.values(err.errors).flat()[0]
            : (err.message || 'No se pudo guardar la ruta');
        Swal.fire('Error', message, 'error');
    });
});

$('#modalRegistroRuta').on('show.bs.modal', function () {
    fetchConductoresRuta(editingRutaId ? $('#conductor').val() : '');

    if (!editingRutaId) {
        setDefaultDatesRuta();
    }
});

$('#modalRegistroRuta').on('hidden.bs.modal', function () {
    editingRutaId = null;
    $('#modalRegistroRutaLabel').html('<i class="fas fa-route"></i> Registrar Ruta');
    $('#formRegistroRuta')[0].reset();
    setDefaultDatesRuta();
    renderUnidadAsignada('');
});
</script>
@endpush
