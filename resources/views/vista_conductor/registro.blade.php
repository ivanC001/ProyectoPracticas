<!-- Modal de Registro -->
<div class="modal fade" id="modalRegistroConductor" tabindex="-1" role="dialog" aria-labelledby="modalRegistroConductorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRegistroConductorLabel">Registrar Conductor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRegistroConductor">
                    @csrf
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
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="activo" name="activo">
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.getElementById('formRegistroConductor').addEventListener('submit', function (event) {
        event.preventDefault(); // Evitar el envío por defecto del formulario

        // Crear un objeto FormData para obtener los datos del formulario
        const formData = new FormData(this);

        // Convertir FormData a un objeto JSON
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // Realizar la solicitud POST a la API
        fetch('/api/conductores', { // Reemplaza con la URL de tu API
            method: 'POST',
            headers: {
                'Content-Type': 'application/json', // Indicar que el contenido es JSON
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Incluir el token CSRF
            },
            body: JSON.stringify(data) // Convertir el objeto a JSON
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la red');
            }
            return response.json();
        })
        .then(data => {
            console.log('Éxito:', data);
            // Aquí puedes agregar lógica para manejar una respuesta exitosa
            // Ejemplo: Cerrar el modal, limpiar el formulario, etc.
            $('#modalRegistroConductor').modal('hide');
            document.getElementById('formRegistroConductor').reset(); // Limpiar el formulario
        })
        .catch((error) => {
            console.error('Error:', error);
            // Aquí puedes agregar lógica para manejar el error
        });
    });
</script>

@endpush
