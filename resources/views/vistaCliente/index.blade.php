@extends('admin.main')

@section('contenido')

<div class="content">
    <div class="container-fluid">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 font-weight-bold">
                        <i class="fas fa-users text-primary"></i> Gestión de Clientes
                    </h4>
                    <small class="text-muted">Administración de clientes registrados</small>
                </div>

                <button class="btn btn-primary shadow-sm"
                        data-toggle="modal"
                        data-target="#modalRegistroCliente">
                    <i class="fas fa-plus"></i> Nuevo Cliente
                </button>
            </div>

            <div class="card-body">

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

            </div>
        </div>

    </div>
</div>

@include('vistaCliente.registro')

@endsection


@push('scripts')
<script>

$(document).ready(function(){
    fetchClientes();
});

/* LISTAR */
function fetchClientes(){

    $.get('/api/clientes', function(res){

        let tbody = $("#clienteTableBody");
        tbody.empty();

        res.data.forEach(c => {

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
                    <td>${c.tipo_doc == 1 ? 'DNI' : 'RUC'}</td>
                    <td>${c.num_doc}</td>
                    <td>${c.razon_social}</td>
                    <td>${c.telefono ?? '-'}</td>
                    <td>${c.email ?? '-'}</td>
                    <td>${c.direccion ?? '-'}</td>
                </tr>
            `);

        });

    });
}

/* ELIMINAR */
function eliminar(id){

    Swal.fire({
        title: '¿Eliminar cliente?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí eliminar'
    }).then((result) => {

        if(result.isConfirmed){

            apiFetch(`/api/clientes/${id}`,{
                method:'DELETE'
            })
            .then(resp => {
                Swal.fire('OK', resp.message, 'success');
                fetchClientes();
            });

        }

    });
}

/* EDITAR */
function editar(id){

    apiFetch(`/api/clientes/${id}`)
    .then(resp => {

        let c = resp.data; // 🔥 IMPORTANTE

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

</script>
@endpush