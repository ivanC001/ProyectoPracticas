<div class="modal fade" id="modalRegistroProducto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalRegistroProductoLabel">Registrar Producto</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <form id="formRegistroProducto">

                    @csrf
                    <input type="hidden" id="productoId">

                    <!-- 🔥 CÓDIGO AUTOMÁTICO -->
                    <div class="form-group">
                        <label>Código (auto generado)</label>
                        <input type="text" class="form-control" id="codigo" readonly placeholder="Se genera automáticamente">
                    </div>

                    <!-- DESCRIPCIÓN -->
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" class="form-control" id="descripcion" required>
                    </div>

                    <!-- CATEGORÍA -->
                    <div class="form-group">
                        <label>Categoría</label>
                        <input type="text" class="form-control" id="categoria" required>
                    </div>

                    <!-- UNIDAD -->
                     <label>Unidad</label>
                    <select class="form-control" id="unidad">
                        
                        <option value="NIU">Unidad (NIU)</option>
                        <option value="LTR">Litro (LTR)</option>
                        <option value="KGM">Kilogramo (KGM)</option>
                    </select>

                    <!-- PRECIO -->
                    <div class="form-group">
                        <label>Precio</label>
                        <input type="number" step="0.01" class="form-control" id="precio" required>
                    </div>

                    <!-- STOCK -->
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" class="form-control" id="stock" required>
                    </div>

                    <!-- ACTIVO SOLO PARA EDICIÓN -->
                    <div class="form-group" id="grupoActivo" style="display:none;">
                        <label>Estado</label>
                        <select class="form-control" id="activo">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-primary" onclick="guardarProducto()">
                        Guardar
                    </button>

                </form>

            </div>

        </div>
    </div>
</div>
<!-- Script para manejar el registro/edición de productos -->
@push('scripts')
<script>
   let editingProductoId = null;

/* 🔥 GUARDAR / ACTUALIZAR */
function guardarProducto(){

    let data = {
        descripcion: $('#descripcion').val(),
        categoria: $('#categoria').val(),
        unidad: $('#unidad').val(),
        precio: $('#precio').val(),
        stock: $('#stock').val()
    };

    // 🔥 SOLO EN EDICIÓN SE ENVÍA ACTIVO
    if(editingProductoId){
        data.activo = $('#activo').val();
    }

    let url = editingProductoId 
        ? `/api/productos/${editingProductoId}` 
        : '/api/productos';

    let method = editingProductoId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(resp => {

        Swal.fire('Éxito','Producto guardado','success');

        $('#modalRegistroProducto').modal('hide');
        $('#formRegistroProducto')[0].reset();

        editingProductoId = null;

        fetchProductos();

    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error','No se pudo guardar','error');
    });

}

/* ✏️ EDITAR */
function editarProducto(id){

    editingProductoId = id;

    fetch(`/api/productos/${id}`)
        .then(res => res.json())
        .then(p => {

            $('#modalRegistroProductoLabel').text('Editar Producto');

            $('#codigo').val(p.codigo);
            $('#descripcion').val(p.descripcion);
            $('#categoria').val(p.categoria);
            $('#unidad').val(p.unidad);
            $('#precio').val(p.precio);
            $('#stock').val(p.stock);
            $('#activo').val(p.activo);

            // 🔥 MOSTRAR ESTADO SOLO EN EDICIÓN
            $('#grupoActivo').show();

            $('#modalRegistroProducto').modal('show');

        });

}

/* 🔄 RESET */
$('#modalRegistroProducto').on('hidden.bs.modal', function () {

    editingProductoId = null;

    $('#modalRegistroProductoLabel').text('Registrar Producto');

    $('#formRegistroProducto')[0].reset();

    $('#codigo').val('');
    $('#grupoActivo').hide();

});
</script>
@endpush
