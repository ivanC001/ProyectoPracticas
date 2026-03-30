@extends('admin.main')

@section('contenido')
<div class="content">
<div class="container-fluid">

<div class="card shadow-sm">

    <!-- HEADER -->
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">
                <i class="fas fa-file-invoice-dollar text-primary"></i> Cotizaciones
            </h4>
            <small class="text-muted">Listado de cotizaciones</small>
        </div>

        <a href="/cotizaciones/registro" class="btn btn-success">
            <i class="fas fa-plus"></i> Nueva Cotización
        </a>
    </div>

    <!-- BODY -->
    <div class="card-body">

        <!-- BUSCADOR -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="buscador"
                       class="form-control"
                       placeholder="Buscar cliente...">
            </div>
        </div>

        <!-- TABLA -->
        <div class="table-responsive">
            <table class="table table-hover table-striped text-center">

                <thead class="bg-primary text-white">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="cotizacionTable"></tbody>

            </table>
        </div>

        <div id="paginacion" class="text-center mt-2"></div>

    </div>

</div>

</div>
</div>
@endsection
@push('scripts')
<script>

let search = '';

$('#buscador').on('input', function(){
    search = $(this).val();
    fetchCotizaciones();
});

function fetchCotizaciones(page=1){

    apiFetch(`/api/cotizaciones?search=${search}&page=${page}`)
    .then(resp=>{

        let html = '';

        resp.data.forEach(c=>{

            html += `
            <tr>

                <td>#${c.id}</td>
                <td>${c.cliente?.razon_social ?? ''}</td>
                <td>${c.fecha}</td>

                <td>
                    <span class="badge badge-info">
                        ${c.detalles.length}
                    </span>
                </td>

                <td><strong>S/ ${c.total}</strong></td>

                <td>
                    <span class="badge badge-secondary">${c.estado}</span>
                </td>

                <td>
                    <button class="btn btn-danger btn-sm"
                        onclick="eliminar(${c.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>

            </tr>`;
        });

        $('#cotizacionTable').html(html);

    });

}

function eliminar(id){

    Swal.fire({
        title:'Eliminar?',
        showCancelButton:true
    }).then(r=>{

        if(r.isConfirmed){

            apiFetch(`/api/cotizaciones/${id}`,{
                method:'DELETE'
            }).then(()=>{
                fetchCotizaciones();
            });

        }

    });

}

$(document).ready(fetchCotizaciones);

</script>
@endpush