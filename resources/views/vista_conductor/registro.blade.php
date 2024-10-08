<!-- Modal de Registro/Edición -->
<div class="modal fade" id="modalRegistroConductor" tabindex="-1" role="dialog" aria-labelledby="modalRegistroConductorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalRegistroConductorLabel">Registrar Conductor</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRegistroConductor">
                    @csrf
                    <input type="hidden" id="conductorId" name="id"> <!-- Campo oculto para el ID del conductor -->
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido:</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                    </div>
                    <div class="form-group">
                        <label for="genero">Género:</label>
                        <select class="form-control" id="genero" name="genero" required>
                            <option value="">Selecciona...</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="licencia">Licencia:</label>
                        <input type="text" class="form-control" id="licencia" name="licencia" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo_licencia">Tipo de Licencia:</label>
                        <select class="form-control" id="tipo_licencia" name="tipo_licencia" required>
                            <option value="">Selecciona...</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" class="form-control" id="telefono" name="telefono">
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" class="form-control" id="direccion" name="direccion">
                    </div>
                    <div class="form-group">
                        <label for="ciudad">Ciudad:</label>
                        <input type="text" class="form-control" id="ciudad" name="ciudad">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editingConductorId = null; // Variable para almacenar el ID del conductor en edición

    document.getElementById('formRegistroConductor').addEventListener('submit', function (event) {
        event.preventDefault(); // Evitar el envío por defecto del formulario

        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // Verificar si estamos editando o creando un nuevo conductor
        const url = editingConductorId ? `/api/conductores/${editingConductorId}` : '/api/conductores';
        const method = editingConductorId ? 'PUT' : 'POST'; // Usar PUT para editar, POST para crear

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la red');
            }
            return response.json();
        })
        .then(data => {
            console.log('Éxito:', data);
            Swal.fire({
                icon: 'success',
                title: 'Registro exitoso',
                text: 'El conductor ha sido registrado correctamente.',
            });
            // Aquí puedes agregar lógica para manejar una respuesta exitosa
            $('#modalRegistroConductor').modal('hide');
            document.getElementById('formRegistroConductor').reset(); // Limpiar el formulario
            editingConductorId = null; // Resetear el ID de edición
            fetchConductores(); // Actualizar la tabla
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    });

    // Función para cargar los datos del conductor en el formulario para editar
    function editar(id) {
        editingConductorId = id; // Guardar el ID del conductor que estamos editando
        $.ajax({
            url: `/api/conductores/${id}`,
            method: 'GET',
            success: function(conductor) {
                // Rellenar el formulario con los datos del conductor
                $('#modalRegistroConductorLabel').text('Editar Conductor');
                $('#nombre').val(conductor.nombre);
                $('#apellido').val(conductor.apellido);
                $('#fecha_nacimiento').val(conductor.fecha_nacimiento);
                $('#genero').val(conductor.genero);
                $('#licencia').val(conductor.licencia);
                $('#tipo_licencia').val(conductor.tipo_licencia);
                $('#telefono').val(conductor.telefono);
                $('#email').val(conductor.email);
                $('#direccion').val(conductor.direccion);
                $('#ciudad').val(conductor.ciudad);
                $('#modalRegistroConductor').modal('show'); // Mostrar el modal
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos del conductor.',
                });
            }
        });
    }

    $(document).ready(function() {
        fetchConductores();

        $("#searchButton").click(function() {
            let searchText = $("#searchText").val();
            if (searchText.trim() !== "") {
                // Aquí podrías agregar la lógica de filtrado si es necesario
                fetchConductores();
            }
        });
    });
</script>
@endpush
