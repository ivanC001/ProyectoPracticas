<!-- Modal de Registro/Edición de Viáticos -->
<div class="modal fade" id="modalRegistroViatico" tabindex="-1" role="dialog" aria-labelledby="modalRegistroViaticoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalRegistroViaticoLabel">Registrar Viático</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRegistroViatico" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="viaticoId" name="id"> <!-- Campo oculto para el ID del viático -->
                    <input type="hidden" name="_method" id="method_field" value="POST"> <!-- Campo oculto para emular PUT -->

                    <!-- Desplegable para seleccionar la Ruta -->
                    <div class="form-group">
                        <label for="ruta">Ruta:</label>
                        <select class="form-control" id="ruta" name="ruta_id" required>
                            <option value="">Seleccione una ruta</option>
                            <!-- Opciones dinámicas con JavaScript -->
                        </select>
                    </div>

                    <!-- Campo para el nombre del servicio -->
                    <div class="form-group">
                        <label for="nombre_servicio">Nombre del Servicio:</label>
                        <input type="text" class="form-control" id="nombre_servicio" name="nombre_servicio" required>
                    </div>

                    <!-- Campo para la fecha del viático -->
                    <div class="form-group">
                        <label for="fecha">Fecha:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" required>
                    </div>

                    <!-- Campo para el número de factura -->
                    <div class="form-group">
                        <label for="numero_factura">Número de Factura:</label>
                        <input type="text" class="form-control" id="numero_factura" name="numero_factura" required>
                    </div>

                    <!-- Campo para el importe -->
                    <div class="form-group">
                        <label for="importe">Importe:</label>
                        <input type="number" step="0.01" class="form-control" id="importe" name="importe" required>
                    </div>

                    <!-- Campo para la descripción -->
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Registrar Viático</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script para manejar el registro/edición de viáticos -->
@push('scripts')
<script>
    let editingViaticoId = null;

    document.getElementById('formRegistroViatico').addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        const method = editingViaticoId ? 'PUT' : 'POST';
        const url = editingViaticoId ? `/api/viaticos/${editingViaticoId}` : '/api/viaticos';

        if (editingViaticoId) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: 'POST', 
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData 
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                icon: 'success',
                title: 'Registro exitoso',
                text: 'El viático ha sido registrado correctamente.',
            });
            $('#modalRegistroViatico').modal('hide');
            document.getElementById('formRegistroViatico').reset();
            editingViaticoId = null;
            fetchViaticos(); 
        })
        .catch((error) => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.message || 'Hubo un problema al registrar el viático.',
            });
        });
    });

    function editarViatico(id) {
        editingViaticoId = id;
        $.ajax({
            url: `/api/viaticos/${id}`,
            method: 'GET',
            success: function(viatico) {
                $('#modalRegistroViaticoLabel').text('Editar Viático');
                $('#nombre_servicio').val(viatico.nombre_servicio);
                $('#fecha').val(viatico.fecha);
                $('#numero_factura').val(viatico.numero_factura);
                $('#importe').val(viatico.importe);
                $('#descripcion').val(viatico.descripcion);
                $('#ruta').val(viatico.ruta_id);
                $('#method_field').val('PUT');
                $('#modalRegistroViatico').modal('show');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos del viático.',
                });
            }
        });
    }

    $('#modalRegistroViatico').on('hidden.bs.modal', function () {
        editingViaticoId = null;
        $('#method_field').val('POST');
        document.getElementById('formRegistroViatico').reset();
    });

    $(document).ready(function() {
        fetchRutas();
        fetchViaticos();
    });

    function fetchRutas() {
        $.ajax({
            url: "/api/rutas",
            method: "GET",
            success: function(rutas) {
                let rutaSelect = $('#ruta');
                rutaSelect.empty().append('<option value="">Seleccione una ruta</option>');
                $.each(rutas, function(index, ruta) {
                    rutaSelect.append(`<option value="${ruta.id}">${ruta.origen} - ${ruta.destino}</option>`);
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
</script>
@endpush