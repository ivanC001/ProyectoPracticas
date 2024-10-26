<!-- Modal de Registro/Edición de Productos -->
<div class="modal fade" id="modalRegistroProducto" tabindex="-1" role="dialog" aria-labelledby="modalRegistroProductoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalRegistroProductoLabel">Registrar Producto</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRegistroProducto" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="productoId" name="id"> <!-- Campo oculto para el ID del producto -->
                    <input type="hidden" name="_method" id="method_field" value="POST"> <!-- Campo oculto para emular PUT -->

                    <!-- Campo para el nombre del producto -->
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>

                    <!-- Campo para la descripción del producto -->
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
                    </div>

                    <!-- Campo para el precio del producto -->
                    <div class="form-group">
                        <label for="precio">Precio:</label>
                        <input type="number" step="0.01" class="form-control" id="precio" name="precio" required>
                    </div>

                    <!-- Campo para la cantidad en stock -->
                    <div class="form-group">
                        <label for="cantidad_stock">Cantidad en Stock:</label>
                        <input type="number" class="form-control" id="cantidad_stock" name="cantidad_stock" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Registrar Producto</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script para manejar el registro/edición de productos -->
@push('scripts')
<script>
    let editingProductoId = null;

    document.getElementById('formRegistroProducto').addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        const method = editingProductoId ? 'PUT' : 'POST';
        const url = editingProductoId ? `/api/productos/${editingProductoId}` : '/api/productos';

        if (editingProductoId) {
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
                text: 'El producto ha sido registrado correctamente.',
            });
            $('#modalRegistroProducto').modal('hide');
            document.getElementById('formRegistroProducto').reset();
            editingProductoId = null;
            fetchProductos(); 
        })
        .catch((error) => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.message || 'Hubo un problema al registrar el producto.',
            });
        });
    });

    function editarProducto(id) {
        editingProductoId = id;
        $.ajax({
            url: `/api/productos/${id}`,
            method: 'GET',
            success: function(producto) {
                $('#modalRegistroProductoLabel').text('Editar Producto');
                $('#nombre').val(producto.nombre);
                $('#descripcion').val(producto.descripcion);
                $('#precio').val(producto.precio);
                $('#cantidad_stock').val(producto.cantidad_stock);
                $('#method_field').val('PUT');
                $('#modalRegistroProducto').modal('show');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos del producto.',
                });
            }
        });
    }

    $('#modalRegistroProducto').on('hidden.bs.modal', function () {
        editingProductoId = null;
        $('#method_field').val('POST');
        document.getElementById('formRegistroProducto').reset();
    });

    $(document).ready(function() {
        fetchProductos(); // Función para cargar productos al iniciar
    });
</script>
@endpush
