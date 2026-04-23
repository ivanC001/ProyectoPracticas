@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="module-shell">
            <div class="card module-card">
                <div class="module-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="module-heading">
                            <div class="module-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div>
                                <h3 class="module-title">Productos</h3>
                                <p class="module-subtitle">Administra catalogo, stock y precios de productos desde una tabla uniforme.</p>
                            </div>
                        </div>

                        <div class="module-header-actions">
                            <button type="button" class="btn btn-success"
                                    data-toggle="modal"
                                    data-target="#modalRegistroProducto">
                                <i class="fas fa-plus-circle"></i> Nuevo producto
                            </button>
                        </div>
                    </div>
                </div>

                <div class="module-body">
                    <div class="module-search mb-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text"
                                   id="searchText"
                                   class="form-control"
                                   placeholder="Buscar por codigo, descripcion o categoria...">
                        </div>
                    </div>

                    <div class="module-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover module-table text-center">
                                <thead>
                                    <tr>
                                        <th>Acciones</th>
                                        <th>ID</th>
                                        <th>Codigo</th>
                                        <th>Descripcion</th>
                                        <th>Categoria</th>
                                        <th>Unidad</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>

                                <tbody id="productoTableBody">
                                    <tr>
                                        <td colspan="9" class="module-empty">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="paginacion" class="module-pagination mt-4"></div>
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
let paginaActual = 1;
let timeoutBusqueda;

$('#searchText').on('keyup', function () {
    let texto = $(this).val();

    clearTimeout(timeoutBusqueda);

    timeoutBusqueda = setTimeout(() => {
        searchGlobal = texto.length < 2 ? '' : texto;
        fetchProductos(1);
    }, 400);
});

function fetchProductos(page = 1) {
    paginaActual = page;

    apiFetch(`/api/productos?search=${searchGlobal}&page=${page}`)
        .then(resp => {
            let tbody = $("#productoTableBody");
            tbody.empty();

            if (resp.data.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="9" class="module-empty">
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
                            <div class="table-action-group">
                                <button type="button" class="btn btn-soft-warning btn-sm" onclick="editarProducto(${p.id})" title="Editar producto">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button type="button" class="btn btn-soft-danger btn-sm" onclick="eliminarProducto(${p.id})" title="Eliminar producto">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>

                        <td>${p.id}</td>
                        <td>${p.codigo}</td>
                        <td class="text-left">${p.descripcion}</td>
                        <td>${p.categoria}</td>
                        <td>${p.unidad}</td>
                        <td>${(p.moneda_precio === 'USD' ? 'US$' : 'S/')} ${parseFloat(p.precio).toFixed(2)}</td>
                        <td>${p.stock}</td>
                        <td>${estado}</td>
                    </tr>
                `);
            });

            renderPaginacion(resp.pagination);
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo cargar productos', 'error');
        });
}

function renderPaginacion(p) {
    let html = '';

    for (let i = 1; i <= p.last_page; i++) {
        html += `
            <button type="button" class="btn btn-sm ${i === p.current_page ? 'btn-primary' : 'btn-light'}"
                onclick="fetchProductos(${i})">
                ${i}
            </button>
        `;
    }

    $('#paginacion').html(html);
}

function eliminarProducto(id) {
    Swal.fire({
        title: 'Eliminar producto?',
        icon: 'warning',
        showCancelButton: true
    }).then(r => {
        if (r.isConfirmed) {
            apiFetch(`/api/productos/${id}`, {
                method: 'DELETE'
            })
            .then(resp => {
                Swal.fire('OK', resp.message, 'success');
                fetchProductos(paginaActual);
            });
        }
    });
}

function editarProducto(id) {
    apiFetch(`/api/productos/${id}`)
        .then(resp => {
            let p = resp.data;

            $('#descripcion').val(p.descripcion);
            $('#categoria').val(p.categoria);
            $('#unidad').val(p.unidad);
            $('#precio').val(p.precio);
            $('#moneda_precio').val(p.moneda_precio || 'PEN');
            $('#stock').val(p.stock);
            $('#activo').val(p.activo);

            window.editingProductoId = id;

            $('#grupoActivo').show();
            $('#modalRegistroProductoLabel').text('Editar producto');
            $('#btnGuardarProducto').text('Guardar cambios');
            $('#modalRegistroProducto').modal('show');
        });
}

$(document).ready(() => {
    fetchProductos();
});
</script>
@endpush
