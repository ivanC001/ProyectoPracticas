@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">
                            Registro de Productos 
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalRegistroProducto">
                                <i class="fas fa-file"></i> Nuevo Producto
                            </button>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <form action="" method="get">
                                <div class="input-group">
                                    <input name="texto" type="text" class="form-control" id="searchText" placeholder="Buscar producto...">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" id="searchButton">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>                      
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="mt-2">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th width="10%">Opciones</th>
                                            <th width="5%">ID</th>
                                            <th width="15%">Nombre</th>
                                            <th width="15%">Descripción</th>
                                            <th width="10%">Precio</th>
                                            <th width="10%">Cantidad en Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productoTableBody">
                                        {{-- Aquí se llenará dinámicamente la tabla con JavaScript --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('gestionProductos.producto.registro') {{-- Modal para registrar nuevo producto --}}

@endsection

@push('scripts')
<script>
    // Función para obtener datos de productos desde la API
    function fetchProductos() {
        $.ajax({
            url: "/api/productos", // Ruta API para obtener productos
            method: "GET",
            success: function(response) {
                let tbody = $("#productoTableBody");
                tbody.empty(); 
                
                // Asegúrate de que 'response' sea un array con productos
                $.each(response, function(index, producto) {
                    tbody.append(`
                        <tr id="producto_${producto.id}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-warning btn-sm editar me-2" onclick="editarProducto(${producto.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-danger btn-sm eliminar" onclick="eliminarProducto(${producto.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                            <td>${producto.id}</td>
                            <td>${producto.nombre}</td>
                            <td>${producto.descripcion}</td>
                            <td>${producto.precio}</td>
                            <td>${producto.cantidad_stock}</td>
                        </tr>
                    `);
                });
            },
            error: function() {
                alert("Error al obtener datos de productos.");
            }
        });
    }

    // Función para eliminar un producto sin recargar la página
    function eliminarProducto(id) {
        Swal.fire({
            title: 'Eliminar registro',
            text: "¿Está seguro de querer eliminar el registro?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/productos/${id}`,
                    method: 'DELETE',
                    headers:{
                      'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Producto eliminado',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $(`#producto_${id}`).remove(); // Elimina la fila del producto
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al eliminar el producto.',
                        });
                    }
                });
            }
        });
    }

    // Limpiar la variable de estado al cerrar el modal
    $('#modalRegistroProducto').on('hidden.bs.modal', function () {
        $('#formRegistroProducto')[0].reset();
        $('#productoId').val(''); // Limpiar el ID del producto
    });

    // Cargar datos de productos al cargar la página
    $(document).ready(function() {
        fetchProductos();
    });
</script>
@endpush
