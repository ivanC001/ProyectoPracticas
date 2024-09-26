<!-- Modal de Registro/Edición de Camión -->
<div class="modal fade" id="modalRegistroCamion" tabindex="-1" role="dialog" aria-labelledby="modalRegistroCamionLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalRegistroCamionLabel">Registrar Camión</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRegistroCamion" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="camionId" name="id"> <!-- Campo oculto para el ID del camión -->
                    <input type="hidden" name="_method" value="POST" id="method_field"> <!-- Campo oculto para emular PUT -->
                    
                    <div class="form-group">
                        <label for="placa_tracto">Placa del Tracto:</label>
                        <input type="text" class="form-control" id="placa_tracto" name="placa_tracto" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="placa_carreto">Placa del Carreto:</label>
                        <input type="text" class="form-control" id="placa_carreto" name="placa_carreto" required>
                    </div>

                    <div class="form-group">
                        <label for="color">Color:</label>
                        <input type="text" class="form-control" id="color" name="color" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha_ingreso">Fecha de Ingreso:</label>
                        <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" required>
                    </div>

                    <div class="form-group">
                        <label for="mtc">MTC:</label>
                        <input type="text" class="form-control" id="mtc" name="mtc" required>
                    </div>

                    <div class="form-group">
                        <label for="foto_camino">Foto del Camión:</label>
                        <input type="file" class="form-control" id="foto_camino" name="foto_camino">
                    </div>

                    <button type="submit" class="btn btn-primary">Registrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editingCamionId  = null; // Variable para almacenar el ID del camión en edición

    // Evento para el formulario de registro/edición de camiones
    document.getElementById('formRegistroCamion').addEventListener('submit', function (event) {
        event.preventDefault(); // Evitar el envío por defecto del formulario

        const formData = new FormData(this);

        // Si estamos editando, agregamos el método PUT en el formData
        if (editingCamionId) {
            formData.append('_method', 'PUT');
        }

        const url = editingCamionId ? `/api/camiones/${editingCamionId}` : '/api/camiones';
        
        fetch(url, {
            method: 'POST', // Siempre usamos POST, usando _method para emular PUT
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData // Enviar el FormData directamente
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la red');
            }
            return response.json();
        })
        .then(data => {
            Swal.fire({
                icon: 'success',
                title: 'Registro exitoso',
                text: 'El camión ha sido registrado correctamente.',
            });
            $('#modalRegistroCamion').modal('hide');
            document.getElementById('formRegistroCamion').reset(); // Limpiar el formulario
            editingCamionId = null; // Resetear el ID de edición
            fetchCamiones(); // Actualizar la tabla
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    });

    // Función para cargar los datos del camión en el formulario para editar
    function editar(id) {
        editingCamionId = id; // Guardar el ID del camión que estamos editando
        $.ajax({
            url: `/api/camiones/${id}`,
            method: 'GET',
            success: function(camion) {
                // Rellenar el formulario con los datos del camión
                $('#modalRegistroCamionLabel').text('Editar Camión');
                $('#placa_tracto').val(camion.placa_tracto);
                $('#placa_carreto').val(camion.placa_carreto);
                $('#color').val(camion.color);
                $('#fecha_ingreso').val(camion.fecha_ingreso);
                $('#mtc').val(camion.mtc);
                $('#modalRegistroCamion').modal('show'); // Mostrar el modal
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos del camión.',
                });
            }
        });
    }

    // Cargar los camiones cuando la página esté lista
    $(document).ready(function() {
        fetchCamiones(); // Cargar la lista de camiones

        // Buscar camiones cuando se hace clic en el botón de búsqueda
        $("#searchButton").click(function() {
            let searchText = $("#searchText").val();
            if (searchText.trim() !== "") {
                fetchCamiones(); // Aquí podrías agregar la lógica de filtrado si es necesario
            }
        });
    });
</script>
@endpush
