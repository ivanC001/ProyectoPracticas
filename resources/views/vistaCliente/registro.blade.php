<div class="modal fade" id="modalRegistroCliente">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">

            <div class="modal-header bg-primary text-white">
                <h5 id="tituloModal" class="mb-0">
                    <i class="fas fa-user-plus"></i> Registrar cliente
                </h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3">
                    <small class="mb-0 d-block">
                        <strong>Campos obligatorios:</strong> Tipo de documento, Numero de documento y Razon social.
                        Los demas campos son opcionales.
                    </small>
                </div>

                <form id="formCliente" novalidate>
                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label for="tipo_doc" class="mb-1">
                                Tipo documento <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="tipo_doc" name="tipo_doc" required>
                                <option value="1">DNI</option>
                                <option value="6">RUC</option>
                                <option value="0">Otro</option>
                            </select>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="num_doc" id="numDocLabel" class="mb-1">
                                Numero de documento <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="num_doc" id="num_doc" required inputmode="numeric" autocomplete="off">
                            <small id="numDocHelp" class="form-text text-muted">Solo numeros.</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="razon_social" id="razonSocialLabel" class="mb-1">
                                Razon social <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="razon_social" id="razon_social" required>
                            <small class="form-text text-muted">Si seleccionas DNI, ingresa nombres y apellidos.</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="telefono" class="mb-1">
                                Telefono <span class="badge badge-light border">Opcional</span>
                            </label>
                            <input type="text" class="form-control" name="telefono" id="telefono" maxlength="20">
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="email" class="mb-1">
                                Email <span class="badge badge-light border">Opcional</span>
                            </label>
                            <input type="email" class="form-control" name="email" id="email" maxlength="255">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="direccion" class="mb-1">
                                Direccion <span class="badge badge-light border">Opcional</span>
                            </label>
                            <input type="text" class="form-control" name="direccion" id="direccion" maxlength="255">
                        </div>

                    </div>
                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="btnGuardar" type="button">
                    Guardar
                </button>
                <button class="btn btn-secondary" data-dismiss="modal" type="button">
                    Cancelar
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
window.clienteEditando = null;
let guardandoCliente = false;

const DOC_RULES = {
    '1': {
        label: 'Numero de DNI',
        placeholder: 'Ingresa 8 digitos',
        help: 'DNI: 8 digitos numericos.',
        maxLength: 8,
        razonLabel: 'Nombres y apellidos'
    },
    '6': {
        label: 'Numero de RUC',
        placeholder: 'Ingresa 11 digitos',
        help: 'RUC: 11 digitos numericos (ej. 20XXXXXXXXX).',
        maxLength: 11,
        razonLabel: 'Razon social'
    },
    '0': {
        label: 'Numero de documento',
        placeholder: 'Ingresa solo numeros',
        help: 'Documento alterno: solo numeros.',
        maxLength: 20,
        razonLabel: 'Nombre o razon social'
    }
};

function aplicarReglasDocumento() {
    const tipo = $('#tipo_doc').val();
    const rule = DOC_RULES[tipo] || DOC_RULES['0'];

    $('#numDocLabel').html(`${rule.label} <span class="text-danger">*</span>`);
    $('#num_doc').attr('placeholder', rule.placeholder);
    $('#num_doc').attr('maxlength', String(rule.maxLength));
    $('#numDocHelp').text(rule.help);
    $('#razonSocialLabel').html(`${rule.razonLabel} <span class="text-danger">*</span>`);
}

function limpiarErrores() {
    $('#formCliente .form-control, #formCliente select').removeClass('is-invalid');
    $('#formCliente .invalid-feedback.dynamic').remove();
}

function mostrarErrores(errors) {
    limpiarErrores();

    Object.keys(errors).forEach(campo => {
        const input = $(`#formCliente [name="${campo}"]`);

        if (!input.length) {
            return;
        }

        input.addClass('is-invalid');
        input.after(`<div class="invalid-feedback dynamic">${errors[campo][0]}</div>`);
    });
}

function recolectarDataCliente() {
    return {
        tipo_doc: ($('#tipo_doc').val() || '').trim(),
        num_doc: ($('#num_doc').val() || '').trim(),
        razon_social: ($('#razon_social').val() || '').trim(),
        telefono: ($('#telefono').val() || '').trim(),
        email: ($('#email').val() || '').trim(),
        direccion: ($('#direccion').val() || '').trim(),
        estado: true
    };
}

function validarClienteFrontend(data) {
    const errors = {};

    if (!data.tipo_doc) {
        errors.tipo_doc = ['El tipo de documento es obligatorio'];
    }

    if (!data.num_doc) {
        errors.num_doc = ['El numero de documento es obligatorio'];
    } else if (!/^\d+$/.test(data.num_doc)) {
        errors.num_doc = ['El numero de documento debe ser numerico'];
    } else if (data.tipo_doc === '1' && data.num_doc.length !== 8) {
        errors.num_doc = ['El DNI debe tener 8 digitos'];
    } else if (data.tipo_doc === '6' && data.num_doc.length !== 11) {
        errors.num_doc = ['El RUC debe tener 11 digitos'];
    }

    if (!data.razon_social) {
        errors.razon_social = ['La razon social es obligatoria'];
    }

    if (data.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
        errors.email = ['Ingresa un email valido'];
    }

    return errors;
}

function setEstadoBotonGuardar(disabled, text = 'Guardar') {
    $('#btnGuardar').prop('disabled', disabled).text(text);
}

$('#tipo_doc').on('change', function () {
    aplicarReglasDocumento();
});

$('#num_doc').on('input', function () {
    this.value = this.value.replace(/\D/g, '');
});

$('#modalRegistroCliente').on('show.bs.modal', function () {
    if (!window.clienteEditando) {
        $('#formCliente')[0].reset();
        $('#tituloModal').html('<i class="fas fa-user-plus"></i> Registrar cliente');
        $('#tipo_doc').val('1');
    }

    aplicarReglasDocumento();
    limpiarErrores();
    setEstadoBotonGuardar(false, 'Guardar');
});

$('#modalRegistroCliente').on('hidden.bs.modal', function () {
    window.clienteEditando = null;
    guardandoCliente = false;
    limpiarErrores();
    setEstadoBotonGuardar(false, 'Guardar');
});

$('#btnGuardar').on('click', function () {
    if (guardandoCliente) {
        return;
    }

    limpiarErrores();

    const data = recolectarDataCliente();
    const erroresFrontend = validarClienteFrontend(data);

    if (Object.keys(erroresFrontend).length > 0) {
        mostrarErrores(erroresFrontend);
        return;
    }

    let url = '/api/clientes';
    let method = 'POST';

    if (window.clienteEditando) {
        url = `/api/clientes/${window.clienteEditando}`;
        method = 'PUT';
    }

    guardandoCliente = true;
    setEstadoBotonGuardar(true, 'Guardando...');

    apiFetch(url, {
        method: method,
        body: JSON.stringify(data)
    })
    .then(resp => {
        Swal.fire('OK', resp.message, 'success');
        $('#modalRegistroCliente').modal('hide');
        fetchClientes();
    })
    .catch(err => {
        if (err.errors) {
            mostrarErrores(err.errors);
        } else {
            Swal.fire('Error', err.message || 'No se pudo guardar el cliente', 'error');
        }
    })
    .finally(() => {
        guardandoCliente = false;
        setEstadoBotonGuardar(false, 'Guardar');
    });
});

</script>
@endpush
