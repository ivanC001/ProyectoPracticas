<!-- Modal de Registro/Edición de Ruta -->
<div class="modal fade" id="modalRegistroRuta" tabindex="-1" role="dialog" aria-labelledby="modalRegistroRutaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalRegistroRutaLabel">Registrar Ruta</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRegistroRuta" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="rutaId" name="id"> <!-- Campo oculto para el ID de la ruta -->
                    <input type="hidden" name="_method" id="method_field" value="POST"> <!-- Campo oculto para emular PUT -->

                    <!-- Desplegable para seleccionar el Conductor -->
                    <div class="form-group">
                        <label for="conductor">Conductor:</label>
                        <select class="form-control" id="conductor" name="conductor_id" required>
                            <option value="">Seleccione un conductor</option>
                            <!-- Opciones dinámicas con JavaScript -->
                        </select>
                    </div>

                    <!-- Desplegable para seleccionar el Camión -->
                    <div class="form-group">
                        <label for="camion">Camión:</label>
                        <select class="form-control" id="camion" name="camion_id" required>
                            <option value="">Seleccione un camión</option>
                            <!-- Opciones dinámicas con JavaScript -->
                        </select>
                    </div>

                    <!-- Campo para la ruta de origen -->
                    <div class="form-group">
                        <label for="origen">Origen:</label>
                        <input type="text" class="form-control" id="origen" name="origen" required>
                    </div>

                    <!-- Campo para la ruta de destino -->
                    <div class="form-group">
                        <label for="destino">Destino:</label>
                        <input type="text" class="form-control" id="destino" name="destino" required>
                    </div>

                    <!-- Campo para la fecha de salida -->
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Salida:</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>

                    <!-- Campo para la fecha de llegada -->
                    <div class="form-group">
                        <label for="fecha_fin">Fecha de Llegada:</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Registrar Ruta</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editingRutaId = null; // Variable para almacenar el ID de la ruta en edición

    // Evento para el formulario de registro/edición de rutas
    document.getElementById('formRegistroRuta').addEventListener('submit', function (event) {
        event.preventDefault(); // Evitar el envío por defecto del formulario

        const formData = new FormData(this);
        const method = editingRutaId ? 'PUT' : 'POST'; // Usar PUT si estamos editando
        const url = editingRutaId ? `/api/rutas/${editingRutaId}` : '/api/rutas';

        // Añadir el campo _method al formData si estamos usando PUT
        if (editingRutaId) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: 'POST', // Siempre usar POST, Laravel manejará PUT en el backend
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData // Enviar los datos del formulario
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                icon: 'success',
                title: 'Registro exitoso',
                text: 'La ruta ha sido registrada correctamente.',
            });
            $('#modalRegistroRuta').modal('hide');
            document.getElementById('formRegistroRuta').reset(); // Limpiar el formulario
            editingRutaId = null; // Resetear el ID de edición
            fetchRutas(); // Actualizar la tabla
        })
        .catch((error) => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al registrar la ruta.',
            });
        });
    });

    // Función para cargar los datos de la ruta en el formulario para editar
    function editar(id) {
        editingRutaId = id; // Guardar el ID de la ruta que estamos editando
        $.ajax({
            url: `/api/rutas/${id}`,
            method: 'GET',
            success: function(ruta) {
                // Rellenar el formulario con los datos de la ruta
                $('#modalRegistroRutaLabel').text('Editar Ruta');
                $('#origen').val(ruta.origen);
                $('#destino').val(ruta.destino);
                $('#fecha_inicio').val(ruta.fecha_inicio);
                $('#fecha_fin').val(ruta.fecha_fin);
                $('#conductor').val(ruta.conductor_id);
                $('#camion').val(ruta.camion_id);
                $('#method_field').val('PUT'); // Cambiar el campo _method a PUT
                $('#modalRegistroRuta').modal('show'); // Mostrar el modal
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

    // Resetear el formulario y el ID cuando se cierre el modal
    $('#modalRegistroRuta').on('hidden.bs.modal', function () {
        editingRutaId = null;
        $('#method_field').val('POST'); // Resetear el método a POST
        document.getElementById('formRegistroRuta').reset(); // Limpiar el formulario
    });

    // Cargar los conductores, camiones y rutas cuando la página esté lista
    $(document).ready(function() {
        fetchConductores(); // Cargar la lista de conductores
        fetchCamiones(); // Cargar la lista de camiones
        fetchRutas(); // Cargar la lista de rutas
    });

    // Función para obtener los camiones y llenar el desplegable
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

    // Función para obtener los conductores y llenar el desplegable
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

    // Función para obtener las rutas y mostrarlas en la tabla (puedes adaptarla para tu vista de rutas)
    function fetchRutas() {
        // Implementación para cargar rutas en la tabla de rutas
    }
</script>
@endpush
