<div class="modal fade" id="modalRegistroCliente">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">

            <div class="modal-header bg-primary text-white">
                <h5 id="tituloModal">
                    <i class="fas fa-user-plus"></i> Registrar Cliente
                </h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <form id="formCliente">
                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label>Tipo Documento</label>
                            <select class="form-control" id="tipo_doc" name="tipo_doc">
                                <option value="1">DNI</option>
                                <option value="6">RUC</option>
                            </select>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label>N° Documento</label>
                            <input type="text" class="form-control" name="num_doc" id="num_doc">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Razón Social</label>
                            <input type="text" class="form-control" name="razon_social" id="razon_social">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="telefono">
                        </div>

                        <div class="col-md-8 mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Dirección</label>
                            <input type="text" class="form-control" name="direccion" id="direccion">
                        </div>

                    </div>
                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="btnGuardar">
                    Guardar
                </button>
                <button class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>

window.clienteEditando = null;

/* LIMPIAR */
function limpiarErrores(){
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

/* MOSTRAR ERRORES BACKEND */
function mostrarErrores(errors){

    limpiarErrores();

    Object.keys(errors).forEach(campo => {

        let input = $(`[name="${campo}"]`);

        if(input.length){
            input.addClass('is-invalid');

            input.after(`
                <div class="invalid-feedback">
                    ${errors[campo][0]}
                </div>
            `);
        }

    });

}

/* ABRIR MODAL */
$('#modalRegistroCliente').on('show.bs.modal', function () {

    if(!window.clienteEditando){
        $('#formCliente')[0].reset();
        $('#tituloModal').html('<i class="fas fa-user-plus"></i> Registrar Cliente');
    }

    limpiarErrores();

});

/* CERRAR */
$('#modalRegistroCliente').on('hidden.bs.modal', function () {
    window.clienteEditando = null;
});

/* GUARDAR */
$('#btnGuardar').on('click', function(){

    limpiarErrores();

    let data = {
        tipo_doc: $('#tipo_doc').val(),
        num_doc: $('#num_doc').val(),
        razon_social: $('#razon_social').val(),
        telefono: $('#telefono').val(),
        email: $('#email').val(),
        direccion: $('#direccion').val(),
        estado: true
    };

    let url = '/api/clientes';
    let method = 'POST';

    if(window.clienteEditando){
        url = `/api/clientes/${window.clienteEditando}`;
        method = 'PUT';
    }

    apiFetch(url,{
        method: method,
        body: JSON.stringify(data)
    })
    .then(resp => {

        Swal.fire('OK', resp.message, 'success');

        $('#modalRegistroCliente').modal('hide');
        fetchClientes();

    })
    .catch(err => {

        if(err.errors){
            mostrarErrores(err.errors);
        }else{
            Swal.fire('Error', err.message, 'error');
        }

    });

});

</script>
@endpush