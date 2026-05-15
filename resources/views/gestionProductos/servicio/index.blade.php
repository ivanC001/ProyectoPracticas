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
                            <i class="fas fa-concierge-bell"></i>
                        </div>
                        <div>
                            <h3 class="module-title">Servicios</h3>
                            <p class="module-subtitle">Mantiene ordenado el catalogo de servicios, precios y condiciones operativas.</p>
                        </div>
                    </div>

                    <div class="module-header-actions">
                        <button type="button" class="btn btn-success"
                        data-toggle="modal"
                        data-target="#modalServicio">
                            <i class="fas fa-plus-circle"></i> Nuevo Servicio
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
                        <input type="text" id="buscador" class="form-control" placeholder="Buscar servicio por nombre, tipo o nivel...">
                    </div>
                </div>

                <div class="module-table-wrap">
                    <div class="table-responsive">
                    <table class="table table-hover module-table text-center">
                        <thead>
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
                </div>

                <div id="paginacion" class="module-pagination mt-4"></div>
            </div>
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
            tbody.html('<tr><td colspan="8" class="module-empty">Sin registros</td></tr>');
            $('#paginacion').html('');
            return;
        }

        resp.data.forEach(s => {
            const moneda = (String(s.moneda_precio || 'PEN').toUpperCase() === 'USD') ? 'USD' : 'PEN';
            const simbolo = moneda === 'USD' ? 'US$' : 'S/';
            tbody.append(`
                <tr>
                    <td>
                        <div class="table-action-group">
                            <button type="button" class="btn btn-soft-warning btn-sm" onclick="editar(${s.id})" title="Editar servicio">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button type="button" class="btn btn-soft-danger btn-sm" onclick="eliminar(${s.id})" title="Eliminar servicio">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                    <td>${s.id}</td>
                    <td class="text-left">${s.nombre}</td>
                    <td>${simbolo} ${parseFloat(s.precio).toFixed(2)} <small class="text-muted d-block">${moneda}</small></td>
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
    if (!p || p.last_page <= 1) {
        $('#paginacion').html('');
        return;
    }

    let html = '';

    for(let i = 1; i <= p.last_page; i++){
        html += `
            <button type="button" class="btn btn-sm ${i === p.current_page ? 'btn-primary' : 'btn-light'}"
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
        $('#moneda_precio').val((String(s.moneda_precio || 'PEN').toUpperCase() === 'USD') ? 'USD' : 'PEN');
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
