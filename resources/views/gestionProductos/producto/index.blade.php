@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">

        <div class="card shadow-sm border-0">

            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-box text-primary"></i> Gestión de Productos
                </h4>

                <button class="btn btn-primary"
                        data-toggle="modal"
                        data-target="#modalRegistroProducto">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </button>
            </div>

            <!-- BODY -->
            <div class="card-body">

                <!-- 🔍 BUSCADOR EN TIEMPO REAL -->
                <div class="mb-3">
                    <div class="input-group shadow-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text"
                               id="searchText"
                               class="form-control"
                               placeholder="Buscar por código, descripción o categoría...">
                    </div>
                </div>

                <!-- 📊 TABLA -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped text-center">

                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Acciones</th>
                                <th>ID</th>
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

                <!-- 🔽 PAGINACIÓN -->
                <div id="paginacion" class="mt-3 text-center"></div>

            </div>
        </div>

    </div>
</div>

@include('gestionProductos.producto.registro')

@endsection


@push('scripts')
<script>

let searchGlobal = '';
let paginaActual = 1;
let timeoutBusqueda;

/* 🔥 BUSCADOR EN TIEMPO REAL */
$('#searchText').on('keyup', function(){

    let texto = $(this).val();

    clearTimeout(timeoutBusqueda);

    timeoutBusqueda = setTimeout(() => {

        // 🔥 si está vacío → muestra todo
        if(texto.length < 2){
            searchGlobal = '';
        }else{
            searchGlobal = texto;
        }

        fetchProductos(1);

    }, 400);

});

/* 🚀 FETCH PRODUCTOS */
function fetchProductos(page = 1){

    paginaActual = page;

    apiFetch(`/api/productos?search=${searchGlobal}&page=${page}`)
    .then(resp => {

        let tbody = $("#productoTableBody");
        tbody.empty();

        if(resp.data.length === 0){
            tbody.html(`
                <tr>
                    <td colspan="9" class="text-center text-muted">
                        No se encontraron resultados
                    </td>
                </tr>
            `);
            $('#paginacion').html('');
            return;
        }

        resp.data.forEach(p => {

            let estado = p.activo == 1
                ? '<span class="badge badge-success">Activo</span>'
                : '<span class="badge badge-danger">Inactivo</span>';

            tbody.append(`
                <tr>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editarProducto(${p.id})">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button class="btn btn-danger btn-sm" onclick="eliminarProducto(${p.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>

                    <td>${p.id}</td>
                    <td>${p.codigo}</td>
                    <td class="text-left">${p.descripcion}</td>
                    <td>${p.categoria}</td>
                    <td>${p.unidad}</td>
                    <td>S/ ${parseFloat(p.precio).toFixed(2)}</td>
                    <td>${p.stock}</td>
                    <td>${estado}</td>
                </tr>
            `);

        });

        renderPaginacion(resp.pagination);

    })
    .catch(()=>{
        Swal.fire('Error','No se pudo cargar productos','error');
    });

}

/* 🔽 PAGINACIÓN */
function renderPaginacion(p){

    let html = '';

    for(let i = 1; i <= p.last_page; i++){
        html += `
            <button class="btn btn-sm ${i === p.current_page ? 'btn-primary' : 'btn-light'}"
                onclick="fetchProductos(${i})">
                ${i}
            </button>
        `;
    }

    $('#paginacion').html(html);
}

/* 🗑️ ELIMINAR */
function eliminarProducto(id){

    Swal.fire({
        title:'¿Eliminar producto?',
        icon:'warning',
        showCancelButton:true
    }).then(r=>{

        if(r.isConfirmed){

            apiFetch(`/api/productos/${id}`,{
                method:'DELETE'
            })
            .then(resp=>{
                Swal.fire('OK', resp.message, 'success');
                fetchProductos(paginaActual);
            });

        }

    });

}

/* ✏️ EDITAR */
function editarProducto(id){

    apiFetch(`/api/productos/${id}`)
    .then(resp => {

        let p = resp.data;

        $('#codigo').val(p.codigo);
        $('#descripcion').val(p.descripcion);
        $('#categoria').val(p.categoria);
        $('#unidad').val(p.unidad);
        $('#precio').val(p.precio);
        $('#stock').val(p.stock);
        $('#activo').val(p.activo);

        window.editingProductoId = id;

        $('#grupoActivo').show();
        $('#modalRegistroProductoLabel').text('Editar Producto');

        $('#modalRegistroProducto').modal('show');

    });

}

/* INIT */
$(document).ready(()=>{
    fetchProductos();
});

</script>
@endpush