<!-- Modal de Registro/Edición de Ruta -->
<div class="modal fade" id="modalRegistroRuta" tabindex="-1" role="dialog" aria-labelledby="modalRegistroRutaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"><!-- modal-lg para más espacio -->
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h2 id="modalRegistroRutaLabel" 
                    class="modal-title fw-bold text-white text-uppercase border-bottom pb-2 d-inline-block"
                    style="border-image: linear-gradient(90deg, #ffffff, #00c6ff) 1; border-image-slice: 1;">
                🚚 Registrar Ruta
                </h2>

                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="formRegistroRuta" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="rutaId" name="id">
                    <input type="hidden" name="_method" id="method_field" value="POST">

                    <h5 class="text-secondary mb-3"><i class="fas fa-road"></i> Datos del viaje</h5>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="origen">Origen:</label>
                            <input type="text" class="form-control" id="origen" name="origen" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="destino">Destino:</label>
                            <input type="text" class="form-control" id="destino" name="destino" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="fecha_inicio">Fecha de Salida:</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="fecha_fin">Fecha de Llegada:</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        </div>
                    </div>

                    <h5 class="text-secondary mt-4 mb-3"><i class="fas fa-user"></i> Responsable del viaje</h5>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="conductor">Conductor:</label>
                            <select class="form-control" id="conductor" name="conductor_id" required>
                                <option value="">Seleccione un conductor</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="camion">Camión:</label>
                            <select class="form-control" id="camion" name="camion_id" required>
                                <option value="">Seleccione un camión</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="text-secondary mt-4 mb-3"><i class="fas fa-dollar-sign"></i> Gastos y estado del viaje</h5>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="caja_chica">Caja Chica:</label>
                            <input type="number" step="0.01" class="form-control" id="caja_chica" name="caja_chica">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pago_viaje">Pago del Viaje:</label>
                            <input type="number" step="0.01" class="form-control" id="pago_viaje" name="pago_viaje">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select class="form-control" id="estado" name="estado" required>
                            <option value="">Seleccione un estado</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en curso">En curso</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones:</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar Ruta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editingRutaId = null; // ID de la ruta en edición

    // Función para establecer fechas por defecto
    function setDefaultDates() {
        let today = new Date().toISOString().split('T')[0];
        let nextWeek = new Date();
        nextWeek.setDate(nextWeek.getDate() + 7);
        let nextWeekFormatted = nextWeek.toISOString().split('T')[0];

        document.getElementById('fecha_inicio').value = today;
        document.getElementById('fecha_fin').value = nextWeekFormatted;
    }

    // Guardar/Editar ruta
    $('#formRegistroRuta').on('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        const url = editingRutaId ? `/api/rutas/${editingRutaId}` : '/api/rutas';

        if (editingRutaId) {
            formData.append('_method', 'PUT'); // Laravel necesita esto para update
        }

        $.ajax({
            url: url,
            method: 'POST', // Laravel interpreta PUT con _method
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Registro exitoso',
                    text: 'La ruta ha sido registrada/actualizada correctamente.',
                });
                $('#modalRegistroRuta').modal('hide');
                $('#formRegistroRuta')[0].reset();
                editingRutaId = null;
                fetchRutas(); // Recargar tabla
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Hubo un problema al registrar la ruta.',
                });
            }
        });
    });

    // Función para editar una ruta
    function editar(id) {
        editingRutaId = id;
        $.ajax({
            url: `/api/rutas/${id}`,
            method: 'GET',
            success: function(ruta) {
                $('#modalRegistroRutaLabel').text('Editar Ruta');

                // Datos del viaje
                $('#origen').val(ruta.origen);
                $('#destino').val(ruta.destino);
                $('#fecha_inicio').val(ruta.fecha_inicio);
                $('#fecha_fin').val(ruta.fecha_fin);

                // Responsable
                $('#conductor').val(ruta.conductor_id);
                $('#camion').val(ruta.camion_id);

                // Gastos y estado
                $('#caja_chica').val(ruta.caja_chica);
                $('#pago_viaje').val(ruta.pago_viaje);
                $('#estado').val(ruta.estado);
                $('#observaciones').val(ruta.observaciones);

                $('#method_field').val('PUT');
                $('#modalRegistroRuta').modal('show');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos de la ruta.',
                });
            }
        });
    }

    // Reset al cerrar modal
    $('#modalRegistroRuta').on('hidden.bs.modal', function () {
        editingRutaId = null;
        $('#method_field').val('POST');
        document.getElementById('formRegistroRuta').reset();
        setDefaultDates();
        $('#modalRegistroRutaLabel').text('Registrar Ruta');
    });

    // Cargar listas al inicio
    $(document).ready(function() {
        fetchConductores();
        fetchCamiones();
        fetchRutas();
        setDefaultDates();
    });

    // Cargar camiones
    function fetchCamiones() {
        $.ajax({
            url: "/api/camiones",
            method: "GET",
            success: function(camiones) {
                let camionSelect = $('#camion');
                camionSelect.empty().append('<option value="">Seleccione un camión</option>');
                $.each(camiones, function(index, camion) {
                    camionSelect.append(`<option value="${camion.id}">${camion.placa_tracto} - ${camion.placa_carreto}</option>`);
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los camiones.',
                });
            }
        });
    }

    // Cargar conductores
    function fetchConductores() {
        $.ajax({
            url: "/api/conductores",
            method: "GET",
            success: function(conductores) {
                let conductorSelect = $('#conductor');
                conductorSelect.empty().append('<option value="">Seleccione un conductor</option>');
                $.each(conductores, function(index, conductor) {
                    conductorSelect.append(`<option value="${conductor.id}">${conductor.nombre} ${conductor.apellido}</option>`);
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los conductores.',
                });
            }
        });
    }
</script>
@endpush
