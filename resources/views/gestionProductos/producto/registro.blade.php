<div class="modal fade" id="modalRegistroProducto">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 id="modalRegistroProductoLabel">Registrar Producto</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <form id="formProducto">

                    <input type="hidden" id="productoId">

                    <div class="form-group">
                        <label>Código</label>
                        <input type="text" class="form-control" id="codigo" readonly>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion">
                    </div>

                    <div class="form-group">
                        <label>Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria">
                    </div>

                    <div class="form-group">
                        <label>Unidad</label>
                        <select class="form-control" id="unidad" name="unidad">
                            <option value="NIU">NIU</option>
                            <option value="LTR">LTR</option>
                            <option value="KGM">KGM</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Precio</label>
                        <input type="number" class="form-control" id="precio" name="precio">
                    </div>

                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock">
                    </div>

                    <div class="form-group" id="grupoActivo" style="display:none;">
                        <label>Estado</label>
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
            mostrarErrores(err.errors); // 🔥 backend real
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

    limpiarErrores();

});

</script>
@endpush