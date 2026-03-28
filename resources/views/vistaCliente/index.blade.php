@extends('admin.main')

@section('contenido')

<div class="content">
    <div class="container-fluid">

        <div class="card shadow">

            <div class="card-header d-flex justify-content-between">
                <h5><i class="fas fa-users"></i> Clientes</h5>

                <button class="btn btn-primary" data-toggle="modal" data-target="#modalRegistroCliente">
                    <i class="fas fa-plus"></i> Nuevo Cliente
                </button>
            </div>

            <div class="card-body">

                <input type="text" id="buscador" class="form-control mb-3"
                       placeholder="Buscar cliente...">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">

                        <thead class="thead-dark">
                            <tr>
                                <th width="12%">Opciones</th>
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

/* 🔥 CONVERTIR */
function tipoDocumento(tipo){
    return tipo == 1 ? "DNI" : "RUC";
}

/* 🔥 LISTAR */
function fetchClientes(){

    $.get('/api/clientes', function(res){

        let tbody = $("#clienteTableBody");
        tbody.empty();

        res.data.forEach(c => {

            tbody.append(`
                <tr>

                    <td>
                        <div class="d-flex">

                            <!-- EDITAR -->
                            <button class="btn btn-warning btn-sm mr-1"
                                    onclick="editar(${c.id})">
                                <i class="fas fa-edit"></i>
                            </button>

                            <!-- ELIMINAR -->
                            <button class="btn btn-danger btn-sm"
                                    onclick="eliminar(${c.id})">
                                <i class="fas fa-trash"></i>
                            </button>

                        </div>
                    </td>

                    <td>${c.id}</td>
                    <td>${tipoDocumento(c.tipo_doc)}</td>
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



function eliminar(id){

    Swal.fire({
        title: '¿Eliminar cliente?',
        text: "Se desactivará el registro",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí eliminar'
    }).then((result) => {

        if(result.isConfirmed){

            $.ajax({
                url: `/api/clientes/${id}`,
                method: 'DELETE',
                headers:{
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(){

                    Swal.fire('OK','Cliente eliminado','success');

                    fetchClientes(); // 🔥 refresca tabla

                }
            });

        }

    });
}

// EDITAR REGISTRO

function editar(id){

    // 🔥 obtener datos del cliente
    $.get(`/api/clientes/${id}`, function(c){

        // 👉 llenar el modal
        $('#tipo_doc').val(c.tipo_doc);
        $('#num_doc').val(c.num_doc);
        $('#razon_social').val(c.razon_social);
        $('#telefono').val(c.telefono);
        $('#email').val(c.email);
        $('#direccion').val(c.direccion);

        // 👉 guardar ID en variable global
        window.clienteEditando = id;

        // 👉 abrir modal
        $('#modalRegistroCliente').modal('show');

    });

}

//🔥 ACTUALIZAR (REUTILIZA TU MODAL)

function guardarCliente(){

    let data = {
        tipo_doc: $('#tipo_doc').val(),
        num_doc: $('#num_doc').val(),
        razon_social: $('#razon_social').val(),
        telefono: $('#telefono').val(),
        email: $('#email').val(),
        direccion: $('#direccion').val(),
        estado: true
    };

    let url = '/api/clientes';
    let method = 'POST';

    // 🔥 SI ESTÁ EDITANDO
    if(window.clienteEditando){
        url = `/api/clientes/${window.clienteEditando}`;
        method = 'PUT';
    }

    fetch(url,{
        method: method,
       
        body: JSON.stringify(data)
    })
    .then(r=>r.json())
    .then(resp=>{

        Swal.fire('OK', resp.message, 'success');

        $('#modalRegistroCliente').modal('hide');

        window.clienteEditando = null;

        fetchClientes();

    });

}

</script>
@endpush