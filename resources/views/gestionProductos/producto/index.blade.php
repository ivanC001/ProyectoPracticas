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

                        <!-- 🔍 BUSCADOR -->
                        <div class="mb-2">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchText" placeholder="Buscar producto...">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info" id="searchButton">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>                      
                                </div>
                            </div>
                        </div>

                        <!-- 📊 TABLA -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="10%">Opciones</th>
                                        <th width="5%">ID</th>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Categoría</th>
                                        <th>Unidad</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>

                                <tbody id="productoTableBody">
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>

                            </table>
                        </div>

                        <!-- 🔽 PAGINACIÓN (placeholder por ahora) -->
                        <div id="paginacion" class="mt-2"></div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('gestionProductos.producto.registro')

@endsection


@push('scripts')
<script>

let searchGlobal = '';

/* 🔍 BUSCADOR */
$('#searchButton').click(function(){
    searchGlobal = $('#searchText').val();
    fetchProductos();
});

/* 🚀 FETCH PRODUCTOS */
function fetchProductos() {

    $.ajax({
        url: "/api/productos",
        method: "GET",
        data: {
            search: searchGlobal
        },
        success: function(response) {

            let tbody = $("#productoTableBody");
            tbody.empty();

            // ⚠️ PREPARADO PARA PAGINACIÓN (cuando venga)
            let productos = response.data ? response.data : response;

            if(productos.length === 0){
                tbody.html(`
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            No hay productos
                        </td>
                    </tr>
                `);
                return;
            }

            $.each(productos, function(index, producto) {

                let estado = producto.activo == 1 
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-danger">Inactivo</span>';

                tbody.append(`
                    <tr id="producto_${producto.id}">
                        <td>
                            <div class="d-flex">
                                <button class="btn btn-warning btn-sm mr-1" onclick="editarProducto(${producto.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="eliminarProducto(${producto.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>

                        <td>${producto.id}</td>
                        <td>${producto.codigo}</td>
                        <td>${producto.descripcion}</td>
                        <td>${producto.categoria}</td>
                        <td>${producto.unidad}</td>
                        <td>S/ ${parseFloat(producto.precio).toFixed(2)}</td>
                        <td>${producto.stock}</td>
                        <td>${estado}</td>
                    </tr>
                `);
            });

            // 🔽 PAGINACIÓN (se activa cuando me pases el controller)
            if(response.meta){
                renderPaginacion(response);
            }

        },
        error: function() {
            Swal.fire('Error','No se pudo cargar productos','error');
        }
    });
}

/* 🗑️ ELIMINAR */
function eliminarProducto(id) {

    Swal.fire({
        title: 'Eliminar producto',
        text: "¿Seguro?",
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: `/api/productos/${id}`,
                method: 'DELETE',
                headers:{
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {

                    Swal.fire('Eliminado','','success');
                    fetchProductos();

                }
            });

        }
    });
}

/* 🔽 PAGINACIÓN (SE ACTIVARÁ CON TU CONTROLLER) */
function renderPaginacion(data){

    let html = '';

    for(let i = 1; i <= data.meta.last_page; i++){
        html += `
            <button class="btn btn-sm ${i === data.meta.current_page ? 'btn-primary' : 'btn-light'}"
                onclick="fetchProductos(${i})">
                ${i}
            </button>
        `;
    }

    $('#paginacion').html(html);
}

/* INIT */
$(document).ready(function(){
    fetchProductos();
});

</script>
@endpush