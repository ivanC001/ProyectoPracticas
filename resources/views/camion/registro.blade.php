<div class="modal fade" id="modalRegistroCamion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="mb-0" id="tituloModalCamion">
                    <i class="fas fa-truck-moving"></i> Registrar Unidad
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="formRegistroCamion">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="fecha_ingreso">Fecha de ingreso *</label>
                            <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="placa_tracto">Placa del tracto *</label>
                            <input type="text" class="form-control text-uppercase" id="placa_tracto" name="placa_tracto" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="placa_carreto">Placa del trailer *</label>
                            <input type="text" class="form-control text-uppercase" id="placa_carreto" name="placa_carreto" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="color">Color *</label>
                            <input type="text" class="form-control" id="color" name="color" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="mtc">Codigo MTC *</label>
                            <input type="text" class="form-control" id="mtc" name="mtc" required>
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="foto_camino">Referencia de foto o archivo</label>
                            <input type="text" class="form-control" id="foto_camino" name="foto_camino" placeholder="Opcional">
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCamion">
                    Guardar unidad
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let editingCamionId = null;

function resetCamionForm() {
    $('#formRegistroCamion')[0].reset();
    $('#tituloModalCamion').html('<i class="fas fa-truck-moving"></i> Registrar Unidad');
    $('#fecha_ingreso').val(new Date().toISOString().slice(0, 10));
    editingCamionId = null;
}

function editarUnidad(id) {
    apiFetch(`/api/camiones/${id}`)
        .then(resp => {
            const camion = resp.data;

            editingCamionId = id;
            $('#tituloModalCamion').html('<i class="fas fa-edit"></i> Editar Unidad');
            $('#fecha_ingreso').val(camion.fecha_ingreso || '');
            $('#placa_tracto').val(camion.placa_tracto || '');
            $('#placa_carreto').val(camion.placa_carreto || '');
            $('#color').val(camion.color || '');
            $('#mtc').val(camion.mtc || '');
            $('#foto_camino').val(camion.foto_camino || '');

            $('#modalRegistroCamion').modal('show');
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo cargar la unidad', 'error');
        });
}

$('#btnGuardarCamion').on('click', function () {
    const payload = {
        fecha_ingreso: $('#fecha_ingreso').val(),
        placa_tracto: $('#placa_tracto').val().trim().toUpperCase(),
        placa_carreto: $('#placa_carreto').val().trim().toUpperCase(),
        color: $('#color').val().trim(),
        mtc: $('#mtc').val().trim().toUpperCase(),
        foto_camino: $('#foto_camino').val().trim()
    };

    const url = editingCamionId ? `/api/camiones/${editingCamionId}` : '/api/camiones';
    const method = editingCamionId ? 'PUT' : 'POST';

    apiFetch(url, {
        method,
        body: JSON.stringify(payload)
    }).then(resp => {
        Swal.fire('OK', resp.message, 'success');
        $('#modalRegistroCamion').modal('hide');
        fetchCamiones(paginaCamionActual);
    }).catch(err => {
        const message = err.errors
            ? Object.values(err.errors).flat()[0]
            : (err.message || 'No se pudo guardar la unidad');
        Swal.fire('Error', message, 'error');
    });
});

$('#modalRegistroCamion').on('show.bs.modal', function () {
    if (!editingCamionId) {
        $('#fecha_ingreso').val(new Date().toISOString().slice(0, 10));
    }
});

$('#modalRegistroCamion').on('hidden.bs.modal', function () {
    resetCamionForm();
});
</script>
@endpush
