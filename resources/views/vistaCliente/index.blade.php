@extends('admin.main')

@section('contenido')

<div class="content">
    <div class="container-fluid">

        <div class="card shadow-sm border-0">

            <!-- HEADER -->
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="mb-0 font-weight-bold">
                        <i class="fas fa-users text-primary"></i> Gestión de Clientes
                    </h3>
                    <button class="btn btn-success"
                        data-toggle="modal"
                        data-target="#modalRegistroCliente">
                    <i class="fas fa-plus-circle"></i> Nuevo Cliente
                </button>
            </div>

            <!-- BODY -->
            <div class="card-body">

                <!-- 🔍 BUSCADOR BACKEND -->
                <div class="mb-3">
                    <input type="text"
                           id="searchText"
                           class="form-control"
                           placeholder="Buscar por documento o nombre...">
                </div>

                <!-- TABLA -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped text-center">

                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Acciones</th>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Documento</th>
                                <th>Razón Social</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Dirección</th>
                            </tr>
                        </thead>

                        <tbody id="clienteTableBody"></tbody>

                    </table>
                </div>

                <!-- 🔽 PAGINACIÓN -->
                <div id="paginacion" class="mt-3 text-center"></div>

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

/* 🔥 BUSCADOR EN TIEMPO REAL (BACKEND) */
$('#searchText').on('input', function(){

    let texto = $(this).val().trim();

    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {

        if(texto.length < 2){
            searchGlobal = '';
        }else{
            searchGlobal = texto;
        }

        fetchClientes(1);

    }, 300);

});

/* 🚀 FETCH CLIENTES */
function fetchClientes(page = 1){

    paginaActual = page;

    apiFetch(`/api/clientes?search=${searchGlobal}&page=${page}`)
    .then(resp => {

        let tbody = $("#clienteTableBody");
        tbody.empty();

        if(resp.data.length === 0){
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        No se encontraron resultados
                    </td>
                </tr>
            `);
            $('#paginacion').html('');
            return;
        }

        resp.data.forEach(c => {

            tbody.append(`
                <tr>
                    <td>
                        <button class="btn btn-warning btn-sm"
                                onclick="editar(${c.id})">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button class="btn btn-danger btn-sm"
                                onclick="eliminar(${c.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>

                    <td>${c.id}</td>
                    <td>
                        <span class="badge badge-${c.tipo_doc == 1 ? 'info':'success'}">
                            ${c.tipo_doc == 1 ? 'DNI' : 'RUC'}
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
    .catch(()=>{
        Swal.fire('Error','No se pudo cargar clientes','error');
    });

}

/* 🔽 PAGINACIÓN */
function renderPaginacion(p){

    let html = '';

    for(let i=1;i<=p.last_page;i++){
        html += `
            <button class="btn btn-sm ${i===p.current_page?'btn-primary':'btn-light'}"
                onclick="fetchClientes(${i})">
                ${i}
            </button>
        `;
    }

    $('#paginacion').html(html);
}

/* 🗑️ ELIMINAR */
function eliminar(id){

    Swal.fire({
        title: '¿Eliminar cliente?',
        icon: 'warning',
        showCancelButton: true
    }).then(r => {

        if(r.isConfirmed){

            apiFetch(`/api/clientes/${id}`,{
                method:'DELETE'
            })
            .then(resp=>{
                Swal.fire('OK', resp.message, 'success');
                fetchClientes(paginaActual);
            });

        }

    });

}

/* ✏️ EDITAR */
function editar(id){

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

        $('#tituloModal').html('<i class="fas fa-edit"></i> Editar Cliente');

        $('#modalRegistroCliente').modal('show');

    });

}

/* INIT */
$(document).ready(()=>{
    fetchClientes();
});

</script>
@endpush