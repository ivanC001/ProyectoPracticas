<div class="modal fade" id="modalRegistroUsuario">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 id="modalRegistroUsuarioLabel">Registrar usuario</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <form id="formUsuario">
                    <input type="hidden" id="usuarioId">

                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>

                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>

                    <div class="form-group">
                        <label>Rol</label>
                        <select class="form-control" id="rol" name="rol">
                            @foreach(config('roles.definitions', []) as $value => $role)
                                <option value="{{ $value }}">{{ $role['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Contrasena</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small id="passwordHelp" class="form-text text-muted">La contrasena debe tener al menos 6 caracteres.</small>
                    </div>

                    <div class="form-group">
                        <label>Confirmar contrasena</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>

                    <div class="form-group" id="grupoActivo" style="display:none;">
                        <label>Estado</label>
                        <select class="form-control" id="activo" name="activo">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="btnGuardarUsuario">Guardar</button>
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.editingUsuarioId = null;

function limpiarErroresUsuario() {
    $('#formUsuario .form-control').removeClass('is-invalid');
    $('#formUsuario .invalid-feedback').remove();
}

function mostrarErroresUsuario(errors) {
    limpiarErroresUsuario();

    Object.keys(errors).forEach((campo) => {
        const input = $(`[name="${campo}"]`);

        if (!input.length) {
            return;
        }

        input.addClass('is-invalid');
        input.after(`<div class="invalid-feedback">${errors[campo][0]}</div>`);
    });
}

$('#btnGuardarUsuario').on('click', function () {
    limpiarErroresUsuario();

    const payload = {
        name: $('#name').val(),
        email: $('#email').val(),
        rol: $('#rol').val(),
        activo: $('#activo').val() === '1',
    };

    const password = $('#password').val();
    const passwordConfirmation = $('#password_confirmation').val();

    if (password) {
        payload.password = password;
        payload.password_confirmation = passwordConfirmation;
    }

    if (!window.editingUsuarioId) {
        payload.password = password;
        payload.password_confirmation = passwordConfirmation;
    }

    const url = window.editingUsuarioId ? `/api/usuarios/${window.editingUsuarioId}` : '/api/usuarios';
    const method = window.editingUsuarioId ? 'PUT' : 'POST';

    apiFetch(url, {
        method,
        body: JSON.stringify(payload)
    }).then((response) => {
        Swal.fire('Listo', response.message, 'success');
        $('#modalRegistroUsuario').modal('hide');
        fetchUsuarios(window.editingUsuarioId ? currentPage : 1);
    }).catch((error) => {
        if (error.errors) {
            mostrarErroresUsuario(error.errors);
            return;
        }

        Swal.fire('Error', error.message || 'No se pudo guardar el usuario', 'error');
    });
});

$('#modalRegistroUsuario').on('show.bs.modal', function () {
    if (!window.editingUsuarioId) {
        $('#passwordHelp').text('La contrasena debe tener al menos 6 caracteres.');
    }
});

$('#modalRegistroUsuario').on('hidden.bs.modal', function () {
    window.editingUsuarioId = null;
    $('#formUsuario')[0].reset();
    $('#grupoActivo').hide();
    $('#modalRegistroUsuarioLabel').text('Registrar usuario');
    limpiarErroresUsuario();
});
</script>
@endpush
