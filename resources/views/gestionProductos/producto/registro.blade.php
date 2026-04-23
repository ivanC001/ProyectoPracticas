<div class="modal fade" id="modalRegistroProducto">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 id="modalRegistroProductoLabel">Registrar producto</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <form id="formProducto">
                    <input type="hidden" id="productoId">

                    <div class="alert alert-light border py-2 mb-3">
                        <small class="text-muted mb-0 d-block">
                            El codigo se genera automaticamente cuando guardas el producto.
                        </small>
                    </div>

                    <div class="form-group mb-2">
                        <label for="descripcion" class="mb-1">Nombre del producto <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="descripcion"
                            name="descripcion"
                            placeholder="Ej. Perno de acero galvanizado"
                        >
                    </div>
                    <small class="text-muted d-block mb-3">Campo obligatorio para identificar el producto.</small>

                    <div class="form-group">
                        <label for="categoria">Categoria</label>
                        <input
                            type="text"
                            class="form-control"
                            id="categoria"
                            name="categoria"
                            placeholder="Ej. Ferreteria"
                        >
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="unidad">Unidad <span class="text-danger">*</span></label>
                                <select class="form-control" id="unidad" name="unidad">
                                    <option value="NIU">NIU</option>
                                    <option value="LTR">LTR</option>
                                    <option value="KGM">KGM</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="stock">Stock inicial <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    class="form-control"
                                    id="stock"
                                    name="stock"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="precio">Precio <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    class="form-control"
                                    id="precio"
                                    name="precio"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                >
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="moneda_precio">Moneda</label>
                                <select class="form-control" id="moneda_precio" name="moneda_precio">
                                    <option value="PEN">Soles (PEN)</option>
                                    <option value="USD">Dolares (USD)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0" id="grupoActivo" style="display:none;">
                        <label for="activo">Estado</label>
                        <select class="form-control" id="activo">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="btnGuardarProducto">
                    Guardar
                </button>
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>

window.editingProductoId = null;

/* LIMPIAR ERRORES */
function limpiarErrores(){
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

/* MOSTRAR ERRORES BACKEND */
function mostrarErrores(errors){

    limpiarErrores();

    Object.keys(errors).forEach(campo => {

        let input = $(`[name="${campo}"]`);

        input.addClass('is-invalid');

        input.after(`
            <div class="invalid-feedback">
                ${errors[campo][0]}
            </div>
        `);
    });
}

/* GUARDAR */
$('#btnGuardarProducto').click(function(){

    limpiarErrores();

    let data = {
        descripcion: $('#descripcion').val(),
        categoria: $('#categoria').val(),
        unidad: $('#unidad').val(),
        precio: $('#precio').val(),
        moneda_precio: $('#moneda_precio').val(),
        stock: $('#stock').val()
    };

    if(editingProductoId){
        data.activo = $('#activo').val();
    }

    let url = editingProductoId
        ? `/api/productos/${editingProductoId}`
        : '/api/productos';

    let method = editingProductoId ? 'PUT' : 'POST';

    apiFetch(url,{
        method: method,
        body: JSON.stringify(data)
    })
    .then(resp => {

        Swal.fire('OK', resp.message, 'success');

        $('#modalRegistroProducto').modal('hide');
        fetchProductos();

    })
    .catch(err => {

        if(err.errors){
            mostrarErrores(err.errors);
        }else{
            Swal.fire('Error', err.message, 'error');
        }

    });

});

/* RESET MODAL */
$('#modalRegistroProducto').on('hidden.bs.modal', function () {

    window.editingProductoId = null;

    $('#formProducto')[0].reset();
    $('#grupoActivo').hide();
    $('#moneda_precio').val('PEN');
    $('#modalRegistroProductoLabel').text('Registrar producto');
    $('#btnGuardarProducto').text('Guardar');

    limpiarErrores();

});

</script>
@endpush

