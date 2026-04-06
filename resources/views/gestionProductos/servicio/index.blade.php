@extends('admin.main')

@section('contenido')

<div class="content">
    <div class="container-fluid">

        <div class="card shadow-sm border-0">

            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 font-weight-bold">
                        <i class="fas fa-concierge-bell text-primary"></i> Gestion de Servicios
                    </h4>
                    <small class="text-muted">Servicios ofrecidos por la empresa</small>
                </div>

                <button class="btn btn-success"
                        data-toggle="modal"
                        data-target="#modalServicio">
                    <i class="fas fa-plus"></i> Nuevo Servicio
                </button>

            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="buscador" class="form-control" placeholder="Buscar servicio por nombre...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped text-center">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Acciones</th>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Duracion</th>
                                <th>Tipo</th>
                                <th>Nivel</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody id="servicioTable"></tbody>
                    </table>
                </div>

                <div id="paginacion" class="mt-2 text-center"></div>
            </div>
        </div>

    </div>
</div>

@include('gestionProductos.servicio.registro')

@endsection


@push('scripts')
<script>
let searchGlobal = '';
let pagina = 1;
let debounce;

$('#buscador').on('input', function(){
    clearTimeout(debounce);

    debounce = setTimeout(() => {
        searchGlobal = $(this).val();
        fetchServicios(1);
    }, 300);
});

function fetchServicios(page = 1){
    pagina = page;

    apiFetch(`/api/servicios?search=${searchGlobal}&page=${page}`)
    .then(resp => {
        let tbody = $("#servicioTable");
        tbody.empty();

        if(resp.data.length === 0){
            tbody.html('<tr><td colspan="8">Sin registros</td></tr>');
            return;
        }

        resp.data.forEach(s => {
            tbody.append(`
                <tr>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editar(${s.id})">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button class="btn btn-danger btn-sm" onclick="eliminar(${s.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                    <td>${s.id}</td>
                    <td class="text-left">${s.nombre}</td>
                    <td>S/ ${parseFloat(s.precio).toFixed(2)}</td>
                    <td>${s.duracion_estimada ?? '-'} min</td>
                    <td>${s.tipo_servicio ?? '-'}</td>
                    <td>${s.nivel_servicio ?? '-'}</td>
                    <td>
                        ${s.activo
                            ? '<span class="badge badge-success">Activo</span>'
                            : '<span class="badge badge-danger">Inactivo</span>'}
                    </td>
                </tr>
            `);
        });

        renderPaginacion(resp.pagination);
    });
}

function renderPaginacion(p){
    let html = '';

    for(let i = 1; i <= p.last_page; i++){
        html += `
            <button class="btn btn-sm ${i === p.current_page ? 'btn-primary' : 'btn-light'}"
                onclick="fetchServicios(${i})">${i}</button>
        `;
    }

    $('#paginacion').html(html);
}

function eliminar(id){
    Swal.fire({
        title:'Eliminar servicio',
        icon:'warning',
        showCancelButton:true
    }).then(r => {
        if(r.isConfirmed){
            apiFetch(`/api/servicios/${id}`,{
                method:'DELETE'
            })
            .then(resp => {
                Swal.fire('OK', resp.message, 'success');
                fetchServicios(pagina);
            });
        }
    });
}

function editar(id){
    apiFetch(`/api/servicios/${id}`)
    .then(resp => {
        let s = resp.data;

        $('#nombre').val(s.nombre);
        $('#descripcion').val(s.descripcion);
        $('#precio').val(s.precio);
        $('#costo').val(s.costo);
        $('#duracion_estimada').val(s.duracion_estimada);
        $('#requiere_personal').val(s.requiere_personal ? 1 : 0);
        $('#cantidad_personal').val(s.cantidad_personal);
        $('#requiere_equipo').val(s.requiere_equipo ? 1 : 0);
        $('#equipos_descripcion').val(s.equipos_descripcion);
        $('#tipo_servicio').val(s.tipo_servicio);
        $('#requiere_transporte').val(s.requiere_transporte ? 1 : 0);
        $('#garantia_dias').val(s.garantia_dias);
        $('#nivel_servicio').val(s.nivel_servicio);
        $('#prioridad').val(s.prioridad);
        $('#frecuencia').val(s.frecuencia);
        $('#condiciones').val(s.condiciones);
        $('#requisitos_cliente').val(s.requisitos_cliente);
        $('#instrucciones').val(s.instrucciones);

        window.servicioEditando = id;

        $('#tituloModal').html('<i class="fas fa-edit"></i> Editar Servicio');
        $('#modalServicio').modal('show');
    });
}

$(document).ready(() => { fetchServicios(); });
</script>
@endpush
