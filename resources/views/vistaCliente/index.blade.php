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
                       placeholder="Buscar...">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="thead-dark">
                            <tr>
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

/* 🔥 SOLO TABLA */
$(document).ready(function(){
    fetchClientes();
});

/* CONVERTIR */
function tipoDocumento(tipo){
    return tipo == 1 ? "DNI" : "RUC";
}

/* LISTAR */
function fetchClientes(){

    $.get('/api/clientes', function(res){

        let tbody = $("#clienteTableBody");
        tbody.empty();

        res.data.forEach(c => {

            tbody.append(`
                <tr>
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

/* 🔥 CLAVE: CUANDO SE CIERRA EL MODAL */
$('#modalRegistroCliente').on('hidden.bs.modal', function () {

    // 👉 cada vez que se cierra el modal recarga tabla
    fetchClientes();

});

</script>
@endpush