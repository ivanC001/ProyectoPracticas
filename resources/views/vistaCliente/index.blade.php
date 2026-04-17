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
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h3 class="module-title">Gestion de clientes</h3>
                                <p class="module-subtitle">Administra datos de clientes con el mismo formato visual del panel.</p>
                            </div>
                        </div>
                        <div class="module-header-actions">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalRegistroCliente">
                                <i class="fas fa-plus-circle"></i> Nuevo cliente
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
                                   placeholder="Buscar por documento o nombre...">
                        </div>
                    </div>

                    <div class="module-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover module-table text-center">
                                <thead>
                                    <tr>
                                        <th>Acciones</th>
                                        <th>ID</th>
                                        <th>Tipo</th>
                                        <th>Documento</th>
                                        <th>Razon social</th>
                                        <th>Telefono</th>
                                        <th>Email</th>
                                        <th>Direccion</th>
                                    </tr>
                                </thead>
                                <tbody id="clienteTableBody">
                                    <tr>
                                        <td colspan="8" class="module-empty">
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

@include('vistaCliente.registro')
@endsection

@push('scripts')
<script>
let searchGlobal = '';
let debounceTimer;
let paginaActual = 1;

$('#searchText').on('input', function () {
    const texto = $(this).val().trim();
    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        searchGlobal = texto.length < 2 ? '' : texto;
        fetchClientes(1);
    }, 300);
});

function fetchClientes(page = 1) {
    paginaActual = page;

    apiFetch(`/api/clientes?search=${encodeURIComponent(searchGlobal)}&page=${page}`)
        .then(resp => {
            const tbody = $("#clienteTableBody");
            tbody.empty();

            if (!resp.data.length) {
                tbody.html(`
                    <tr>
                        <td colspan="8" class="module-empty">No se encontraron resultados</td>
                    </tr>
                `);
                $('#paginacion').html('');
                return;
            }

            resp.data.forEach(c => {
                tbody.append(`
                    <tr>
                        <td>
                            <div class="table-action-group">
                                <button type="button" class="btn btn-soft-warning btn-sm" onclick="editar(${c.id})" title="Editar cliente">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-soft-danger btn-sm" onclick="eliminar(${c.id})" title="Eliminar cliente">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                        <td>${c.id}</td>
                        <td>
                            <span class="badge badge-${c.tipo_doc == 1 ? 'info' : 'success'}">
                                ${c.tipo_doc == 1 ? 'DNI' : (c.tipo_doc == 6 ? 'RUC' : 'OTRO')}
                            </span>
                        </td>
                        <td>${c.num_doc}</td>
                        <td class="text-left">${c.razon_social}</td>
                        <td>${c.telefono ?? '-'}</td>
                        <td>${c.email ?? '-'}</td>
                        <td class="text-left">${c.direccion ?? '-'}</td>
                    </tr>
                `);
            });

            renderPaginacion(resp.pagination);
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo cargar clientes', 'error');
        });
}

function renderPaginacion(p) {
    if (!p || p.last_page <= 1) {
        $('#paginacion').html('');
        return;
    }

    let html = '';

    for (let i = 1; i <= p.last_page; i++) {
        html += `
            <button type="button" class="btn btn-sm ${i === p.current_page ? 'btn-primary' : 'btn-light'}"
                onclick="fetchClientes(${i})">
                ${i}
            </button>
        `;
    }

    $('#paginacion').html(html);
}

function eliminar(id) {
    Swal.fire({
        title: 'Eliminar cliente?',
        text: 'Esta accion desactiva el cliente.',
        icon: 'warning',
        showCancelButton: true
    }).then(r => {
        if (!r.isConfirmed) {
            return;
        }

        apiFetch(`/api/clientes/${id}`, {
            method: 'DELETE'
        })
        .then(resp => {
            Swal.fire('OK', resp.message, 'success');
            fetchClientes(paginaActual);
        });
    });
}

function editar(id) {
    apiFetch(`/api/clientes/${id}`)
        .then(resp => {
            let c = resp.data;

            $('#tipo_doc').val(c.tipo_doc);
            $('#num_doc').val(c.num_doc);
            $('#razon_social').val(c.razon_social);
            $('#telefono').val(c.telefono);
            $('#email').val(c.email);
            $('#direccion').val(c.direccion);

            window.clienteEditando = id;
            $('#tituloModal').html('<i class="fas fa-edit"></i> Editar cliente');
            $('#modalRegistroCliente').modal('show');
        });
}

$(document).ready(() => {
    fetchClientes();
});
</script>
@endpush
