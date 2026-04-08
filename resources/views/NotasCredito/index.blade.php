@extends('admin.main')

@section('contenido')

<div class="content">
<div class="container-fluid">

<div class="card shadow">

<div class="card-header d-flex justify-content-between">

    <h5><i class="fas fa-file-alt"></i> Notas</h5>

    <button class="btn btn-primary" data-toggle="modal" data-target="#modalNota">
        Nueva Nota
    </button>

</div>

<div class="card-body">

<table class="table table-bordered">

<thead>
<tr>
    <th>#</th>
    <th>Comprobante</th>
    <th>Tipo</th>
    <th>Motivo</th>
    <th>Estado</th>
</tr>
</thead>

<tbody id="tablaNotas"></tbody>

</table>

</div>
</div>

</div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalNota">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header bg-primary text-white">
    <h5>Nueva Nota</h5>
</div>

<div class="modal-body">
    @include('NotasCredito.registro')
</div>

<div class="modal-footer">
    <button class="btn btn-success" onclick="procesarNota()">Registrar</button>
</div>

</div>
</div>
</div>

@endsection

@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', cargarNotas);

function cargarNotas(){

    apiFetch('/api/facturacion/notas')
    .then(data=>{

        let tbody = document.getElementById('tablaNotas');
        tbody.innerHTML = '';

        (data.data || []).forEach(n=>{

            let tipo = n.tipo_documento == '07' ? 'Crédito' : 'Débito';

            tbody.innerHTML += `
                <tr>
                    <td>${n.id}</td>
                    <td>${n.numero_comprobante}</td>
                    <td>${tipo}</td>
                    <td>${n.desMotivo}</td>
                    <td>${n.estado_envio}</td>
                </tr>
            `;
        });

    });

}

</script>
@endpush