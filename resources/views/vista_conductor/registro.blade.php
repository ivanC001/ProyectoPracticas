<div class="modal fade" id="modalRegistroConductor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="mb-0" id="tituloModalConductor">
                    <i class="fas fa-id-card-alt"></i> Registrar Conductor
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="formRegistroConductor">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="nombre">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="apellido">Apellido *</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="fecha_nacimiento">Fecha de nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="genero">Genero *</label>
                            <select class="form-control" id="genero" name="genero" required>
                                <option value="">Seleccione...</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="ciudad">Ciudad</label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad">
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="tipo_licencia">Tipo de licencia *</label>
                            <select class="form-control" id="tipo_licencia" name="tipo_licencia" required>
                                <option value="">Seleccione...</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="licencia">Numero de licencia *</label>
                            <input type="text" class="form-control" id="licencia" name="licencia" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="telefono">Telefono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono">
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="direccion">Direccion</label>
                            <input type="text" class="form-control" id="direccion" name="direccion">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="camion_id">Tracto y trailer asignados *</label>
                            <select class="form-control" id="camion_id" name="camion_id" required>
                                <option value="">Seleccione una unidad</option>
                            </select>
                            <small class="text-muted">Cada conductor debe salir con una unidad asignada.</small>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarConductor">
                    Guardar conductor
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let editingConductorId = null;

function resetConductorForm() {
    $('#formRegistroConductor')[0].reset();
    $('#tituloModalConductor').html('<i class="fas fa-id-card-alt"></i> Registrar Conductor');
    editingConductorId = null;
}

function fetchUnidadesConductor(selectedId = '') {
    return apiFetch('/api/camiones?per_page=200')
        .then(resp => {
            const select = $('#camion_id');
            select.empty().append('<option value="">Seleccione una unidad</option>');

            (resp.data || []).forEach(camion => {
                const selected = Number(selectedId) === Number(camion.id) ? 'selected' : '';
                select.append(`
                    <option value="${camion.id}" ${selected}>
                        ${camion.placa_tracto} / ${camion.placa_carreto} - ${camion.color}
                    </option>
                `);
            });
        });
}

function editar(id) {
    Promise.all([
        apiFetch(`/api/conductores/${id}`),
        fetchUnidadesConductor()
    ]).then(([resp]) => {
        const conductor = resp.data;

        editingConductorId = id;
        $('#tituloModalConductor').html('<i class="fas fa-edit"></i> Editar Conductor');
        $('#nombre').val(conductor.nombre || '');
        $('#apellido').val(conductor.apellido || '');
        $('#fecha_nacimiento').val(conductor.fecha_nacimiento || '');
        $('#genero').val(conductor.genero || '');
        $('#licencia').val(conductor.licencia || '');
        $('#tipo_licencia').val(conductor.tipo_licencia || '');
        $('#telefono').val(conductor.telefono || '');
        $('#email').val(conductor.email || '');
        $('#direccion').val(conductor.direccion || '');
        $('#ciudad').val(conductor.ciudad || '');
        $('#camion_id').val(conductor.camion_id || '');

        $('#modalRegistroConductor').modal('show');
    }).catch(() => {
        Swal.fire('Error', 'No se pudieron cargar los datos del conductor', 'error');
    });
}

$('#btnGuardarConductor').on('click', function () {
    const payload = {
        nombre: $('#nombre').val().trim(),
        apellido: $('#apellido').val().trim(),
        fecha_nacimiento: $('#fecha_nacimiento').val() || null,
        genero: $('#genero').val(),
        licencia: $('#licencia').val().trim(),
        tipo_licencia: $('#tipo_licencia').val(),
        telefono: $('#telefono').val().trim(),
        email: $('#email').val().trim(),
        direccion: $('#direccion').val().trim(),
        ciudad: $('#ciudad').val().trim(),
        camion_id: $('#camion_id').val()
    };

    const url = editingConductorId ? `/api/conductores/${editingConductorId}` : '/api/conductores';
    const method = editingConductorId ? 'PUT' : 'POST';

    apiFetch(url, {
        method,
        body: JSON.stringify(payload)
    }).then(resp => {
        Swal.fire('OK', resp.message, 'success');
        $('#modalRegistroConductor').modal('hide');
        fetchConductores(paginaConductorActual);
    }).catch(err => {
        const message = err.errors
            ? Object.values(err.errors).flat()[0]
            : (err.message || 'No se pudo guardar el conductor');
        Swal.fire('Error', message, 'error');
    });
});

$('#modalRegistroConductor').on('show.bs.modal', function () {
    fetchUnidadesConductor(editingConductorId ? $('#camion_id').val() : '');
});

$('#modalRegistroConductor').on('hidden.bs.modal', function () {
    resetConductorForm();
});
</script>
@endpush
