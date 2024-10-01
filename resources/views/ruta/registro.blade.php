<!-- Modal de Registro/Edición de Combustible -->
<div class="modal fade" id="modalRegistroCombustible" tabindex="-1" role="dialog" aria-labelledby="modalRegistroCombustibleLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalRegistroCombustibleLabel">Registrar Combustible</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRegistroCombustible" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="combustibleId" name="id"> <!-- Campo oculto para el ID del combustible -->
                    <input type="hidden" name="_method" id="method_field" value="POST"> <!-- Campo oculto para emular PUT -->

                    <!-- Campo para la ruta de origen/destino -->
                    <div class="form-group">
                        <label for="ruta">Ruta:</label>
                        <select class="form-control" id="ruta" name="ruta_id" required>
                            <option value="">Seleccione una ruta</option>
                            <!-- Opciones dinámicas con JavaScript -->
                        </select>
                    </div>

                    <!-- Campo para el número de factura -->
                    <div class="form-group">
                        <label for="num_factura">Número de Factura:</label>
                        <input type="text" class="form-control" id="num_factura" name="num_factura" required>
                    </div>

                    <!-- Campo para el grifo -->
                    <div class="form-group">
                        <label for="grifo">Grifo:</label>
                        <input type="text" class="form-control" id="grifo" name="grifo" required>
                    </div>

                    <!-- Campo para la fecha y hora -->
                    <div class="form-group">
                        <label for="fecha_hora">Fecha y Hora:</label>
                        <input type="datetime-local" class="form-control" id="fecha_hora" name="fecha_hora" required>
                    </div>

                    <!-- Campo para los galones de combustible -->
                    <div class="form-group">
                        <label for="galonesCombustible">Galones de Combustible:</label>
                        <input type="number" class="form-control" id="galonesCombustible" name="galonesCombustible" required>
                    </div>

                    <!-- Campo para el importe -->
                    <div class="form-group">
                        <label for="importe">Importe:</label>
                        <input type="number" step="0.01" class="form-control" id="importe" name="importe" required>
                    </div>

                    <!-- Campo para el kilometraje inicial -->
                    <div class="form-group">
                        <label for="kilometraje_inicial">Kilometraje Inicial:</label>
                        <input type="number" class="form-control" id="kilometraje_inicial" name="kilometraje_inicial" required>
                    </div>

                    <!-- Campo para el kilometraje final -->
                    <div class="form-group">
                        <label for="kilometraje_final">Kilometraje Final:</label>
                        <input type="number" class="form-control" id="kilometraje_final" name="kilometraje_final" required>
                    </div>

                    <!-- Campo para el tipo de combustible -->
                    <div class="form-group">
                        <label for="tipo_combustible">Tipo de Combustible:</label>
                        <input type="text" class="form-control" id="tipo_combustible" name="tipo_combustible" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Registrar Combustible</button>
                </form>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    let editingCombustibleId = null; // Variable para almacenar el ID del combustible en edición

    // Evento para el formulario de registro/edición de combustibles
    document.getElementById('formRegistroCombustible').addEventListener('submit', function (event) {
        event.preventDefault(); // Evitar el envío por defecto del formulario

        const formData = new FormData(this);
        const method = editingCombustibleId ? 'PUT' : 'POST'; // Usar PUT si estamos editando
        const url = editingCombustibleId ? `/api/combustibles/${editingCombustibleId}` : '/api/combustibles';

        // Añadir el campo _method al formData si estamos usando PUT
        if (editingCombustibleId) {
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
                text: 'El combustible ha sido registrado correctamente.',
            });
            $('#modalRegistroCombustible').modal('hide');
            document.getElementById('formRegistroCombustible').reset(); // Limpiar el formulario
            editingCombustibleId = null; // Resetear el ID de edición
            fetchCombustibles(); // Actualizar la tabla de combustibles
        })
        .catch((error) => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.message || 'Hubo un problema al registrar el combustible.',
            });
        });
    });

    // Función para cargar los datos del combustible en el formulario para editar
    function editar(id) {
        editingCombustibleId = id; // Guardar el ID del combustible que estamos editando
        $.ajax({
            url: `/api/combustibles/${id}`,
            method: 'GET',
            success: function(combustible) {
                // Rellenar el formulario con los datos del combustible
                $('#modalRegistroCombustibleLabel').text('Editar Combustible');
                $('#num_factura').val(combustible.num_factura);
                $('#grifo').val(combustible.grifo);
                $('#fecha_hora').val(combustible.fecha_hora);
                $('#galonesCombustible').val(combustible.galonesCombustible);
                $('#importe').val(combustible.importe);
                $('#kilometraje_inicial').val(combustible.kilometraje_inicial);
                $('#kilometraje_final').val(combustible.kilometraje_final);
                $('#tipo_combustible').val(combustible.tipo_combustible);
                $('#ruta').val(combustible.ruta_id); // Seleccionar la ruta correspondiente
                $('#method_field').val('PUT'); // Cambiar el campo _method a PUT
                $('#modalRegistroCombustible').modal('show'); // Mostrar el modal
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos del combustible.',
                });
            }
        });
    }

    // Resetear el formulario y el ID cuando se cierre el modal
    $('#modalRegistroCombustible').on('hidden.bs.modal', function () {
        editingCombustibleId = null;
        $('#method_field').val('POST'); // Resetear el método a POST
        document.getElementById('formRegistroCombustible').reset(); // Limpiar el formulario
    });

    // Cargar las rutas y combustibles cuando la página esté lista
    $(document).ready(function() {
        fetchRutas(); // Cargar la lista de rutas
        fetchCombustibles(); // Cargar la lista de combustibles
    });

    // Función para obtener las rutas y llenar el desplegable
    function fetchRutas() {
        $.ajax({
            url: "/api/rutas",
            method: "GET",
            success: function(rutas) {
                let rutaSelect = $('#ruta');
                rutaSelect.empty().append('<option value="">Seleccione una ruta</option>');
                $.each(rutas, function(index, ruta) {
                    rutaSelect.append(`<option value="${ruta.id}">Ruta ${ruta.id} - De ${ruta.origen} a ${ruta.destino}</option>`); // Se usa el ID de la ruta
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar las rutas.',
                });
            }
        });
    }

    // Función para obtener los combustibles y mostrarlos en la tabla
    function fetchCombustibles() {
        // Implementación para cargar combustibles en la tabla de combustibles
    }

</script>
@endpush
