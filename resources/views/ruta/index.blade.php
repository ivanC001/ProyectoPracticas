@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">

        <div class="card">

            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="text-primary font-weight-bold m-0">
                        <i class="fas fa-route"></i> Registro de Rutas
                    </h3>

                    <button class="btn btn-success"
                            data-toggle="modal"
                            data-target="#modalRegistroRuta">
                        <i class="fas fa-plus-circle"></i> Nueva Ruta
                    </button>
                </div>
            </div>

            <div class="card-body">

                <!-- 🔥 BUSCADOR BACKEND -->
                <div class="mb-3">
                    <input type="text"
                           id="buscador"
                           class="form-control text-center"
                           placeholder="Buscar por origen o destino...">
                </div>

                <!-- TABLA (TU FORMATO ORIGINAL) -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover table-sm align-middle text-center">

                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="5%">Opciones</th>
                                <th width="5%">ID</th>
                                <th width="15%">Fechas del viaje</th>
                                <th width="20%">Ruta y Conductor</th>
                                <th width="20%">Datos del vehículo</th>
                                <th width="13%">Gastos de viaje</th>
                                <th width="8%">Estado</th>
                                <th width="20%">Costo de Viaje</th>
                                <th width="8%">Reporte</th>
                            </tr>
                        </thead>

                        <tbody id="rutaTableBody"></tbody>

                    </table>
                </div>

                <!-- 🔽 PAGINACIÓN -->
                <div id="paginacion" class="mt-3 text-center"></div>

            </div>
        </div>

    </div>
</div>

@include('ruta.registro')

@endsection
@push('scripts')
<script>

let searchGlobal = '';
let debounceTimer;
let paginaActual = 1;

/* 🔥 BUSCADOR BACKEND */
$('#buscador').on('input', function(){

    let texto = $(this).val().trim();

    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {

        searchGlobal = texto;
        fetchRutas(1);

    }, 300);

});

/* 🚀 FETCH */
function fetchRutas(page = 1){

    paginaActual = page;

    apiFetch(`/api/rutas?search=${searchGlobal}&page=${page}`)
    .then(resp => {

        let tbody = $("#rutaTableBody");
        tbody.empty();

        if(resp.data.length === 0){
            tbody.html(`<tr><td colspan="9">Sin resultados</td></tr>`);
            return;
        }

        resp.data.forEach(rutas => {

            tbody.append(`
                <tr id="ruta_${rutas.id}">

                    <td>
                        <div class="d-flex justify-content-center">
                            <button class="btn btn-warning btn-sm mr-1"
                                    onclick="editar(${rutas.id})">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button class="btn btn-danger btn-sm"
                                    onclick="eliminar(${rutas.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>

                    <td><strong>${rutas.id}</strong></td>

                    <td>
                        <div class="border rounded p-2 bg-light">
                            <i class="fas fa-calendar-day text-primary"></i>
                            <strong>Salida:</strong><br> ${rutas.fecha_inicio ?? 'N/A'}<br>

                            <i class="fas fa-calendar-check text-success"></i>
                            <strong>Llegada:</strong><br> ${rutas.fecha_fin ?? 'N/A'}
                        </div>
                    </td>

                    <td>
                        <div class="border rounded p-2 bg-light">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <strong>Origen:</strong><br> ${rutas.origen ?? 'N/A'}<br>

                            <i class="fas fa-flag-checkered text-info"></i>
                            <strong>Destino:</strong><br> ${rutas.destino ?? 'N/A'}<br>

                            <i class="fas fa-user text-warning"></i>
                            <strong>Conductor:</strong><br> ${rutas.conductor?.nombre ?? 'N/A'}
                        </div>
                    </td>

                    <td>
                        <div class="border rounded p-2 bg-light">
                            <i class="fas fa-truck text-secondary"></i>
                            <strong>Tracto:</strong><br> ${rutas.camion?.placa_tracto ?? 'N/A'}<br>

                            <i class="fas fa-trailer text-primary"></i>
                            <strong>Carreto:</strong><br> ${rutas.camion?.placa_carreto ?? 'N/A'}
                        </div>
                    </td>

                    <td>
                        <div class="d-flex flex-column">
                            <button class="btn btn-info btn-sm mb-1" onclick="registrarViaticos(${rutas.id})">
                                <i class="fas fa-wallet"></i> Viáticos
                            </button>
                            <button class="btn btn-secondary btn-sm mb-1" onclick="registrarPeajes(${rutas.id})">
                                <i class="fas fa-road"></i> Peajes
                            </button>
                            <button class="btn btn-success btn-sm" onclick="registrarCombustible(${rutas.id})">
                                <i class="fas fa-gas-pump"></i> Combustible
                            </button>
                        </div>
                    </td>

                    <td>${getEstadoBadge(rutas.estado)}</td>

                    <td>
                        <div class="border rounded p-2 bg-light">
                            <strong>Pago:</strong> S/ ${rutas.pago_viaje ?? '0.00'}<br>
                            <strong>Caja:</strong> S/ ${rutas.caja_chica ?? '0.00'}
                        </div>
                    </td>

                    <td>
                        <a class="btn btn-primary btn-sm" href="/api/reporte/ruta/${rutas.id}">
                            <i class="fas fa-file-alt"></i>
                        </a>
                    </td>

                </tr>
            `);

        });

        renderPaginacion(resp.pagination);

    })
    .catch(err=>{
        Swal.fire('Error', err.message || 'Error al cargar rutas', 'error');
    });

}

/* 🔽 PAGINACIÓN */
function renderPaginacion(p){

    let html = '';

    for(let i=1;i<=p.last_page;i++){
        html += `
            <button class="btn btn-sm ${i===p.current_page?'btn-primary':'btn-light'}"
                onclick="fetchRutas(${i})">${i}</button>
        `;
    }

    $('#paginacion').html(html);
}

/* 🎨 ESTADO */
function getEstadoBadge(estado){

    estado = (estado || '').toLowerCase();

    switch (estado) {
        case 'pendiente':
            return '<span class="badge bg-success">Pendiente</span>';
        case 'cancelado':
            return '<span class="badge bg-danger">Cancelado</span>';
        case 'finalizado':
            return '<span class="badge bg-primary">Finalizado</span>';
        case 'en curso':
            return '<span class="badge bg-warning text-dark">En curso</span>';
        default:
            return '<span class="badge bg-secondary">N/A</span>';
    }
}

/* 🗑️ ELIMINAR */
function eliminar(id){

    Swal.fire({
        title:'¿Eliminar?',
        icon:'warning',
        showCancelButton:true
    }).then(r=>{

        if(r.isConfirmed){

            apiFetch(`/api/rutas/${id}`,{
                method:'DELETE'
            })
            .then(resp=>{
                Swal.fire('OK', resp.message, 'success');
                fetchRutas(paginaActual);
            });

        }

    });

}

/* REDIRECCIONES */
function registrarViaticos(id){
    window.location.href = `/ruta/${id}/rutaviatico`;
}

function registrarPeajes(id){
    window.location.href = `/ruta/${id}/rutapeaje`;
}

function registrarCombustible(id){
    window.location.href = `/ruta/${id}/rutacombustible`;
}

/* INIT */
$(document).ready(()=>{
    fetchRutas();
});

</script>
@endpush