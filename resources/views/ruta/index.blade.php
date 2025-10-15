@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="text-primary font-weight-bold m-0">
                                <i class="fas fa-route"></i> Registro de Rutas
                            </h3>
                            <button class="btn btn-success shadow-sm" data-toggle="modal" data-target="#modalRegistroRuta">
                                <i class="fas fa-plus-circle"></i> Nueva Ruta
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Buscador -->
                        <div class="mb-3">
                            <form action="" method="get">
                                <input class="form-control me-2 text-center" type="search" placeholder="Buscar Ruta por origen o destino..."
                                    aria-label="Search" id="buscador">
                            </form>
                        </div>

                        <!-- Tabla -->
                       
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-sm align-middle text-center">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%">Opciones</th>
                                        <th width="5%">ID</th>
                                        <th width="15%">Fechas del viaje</th>
                                        <th width="20%">Ruta y Conductor</th>
                                        <th width="20%">Datos del vehículo</th>
                                        <th width="13%">Gastos de viaje</th> <!-- Nueva columna -->
                                        <th width="8%">Estado</th> <!-- Nueva columna -->
                                        <th width="20%">Costo de Viaje</th>
                                        <th width="8%">Reporte</th>
                                    </tr>
                                </thead>
                                <tbody id="rutaTableBody">
                                    {{-- Llenado dinámico con JS --}}
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('ruta.registro')
@endsection

@push('scripts')
<script>
    function fetchRutas() {
        $.ajax({
            url: "/api/rutas",
            method: "GET",
            success: function(response) {
                let tbody = $("#rutaTableBody");
                tbody.empty();

                let rutasInvertidas = response.reverse();

                $.each(rutasInvertidas, function(index, rutas) {
                    tbody.append(`
                        <tr id="ruta_${rutas.id}">
                            <!-- Opciones básicas -->
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-warning btn-sm editar" onclick="editar(${rutas.id})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm eliminar" onclick="eliminar(${rutas.id})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>

                            <!-- ID -->
                            <td><strong>${rutas.id}</strong></td>

                            <!-- Fechas -->
                            <td>
                                <div class="border rounded p-2 bg-light">
                                    <i class="fas fa-calendar-day text-primary"></i> <strong>Salida:</strong><br> ${rutas.fecha_inicio ?? 'N/A'}<br>
                                    <i class="fas fa-calendar-check text-success"></i> <strong>Llegada:</strong><br> ${rutas.fecha_fin ?? 'N/A'}
                                </div>
                            </td>

                            <!-- Ruta y Conductor -->
                            <td>
                                <div class="border rounded p-2 bg-light">
                                    <i class="fas fa-map-marker-alt text-danger"></i> <strong>Origen:</strong><br> ${rutas.origen ?? 'N/A'}<br>
                                    <i class="fas fa-flag-checkered text-info"></i> <strong>Destino:</strong><br> ${rutas.destino ?? 'N/A'}<br>
                                    <i class="fas fa-user text-warning"></i> <strong>Conductor:</strong><br> ${rutas.conductor ? rutas.conductor.nombre : 'N/A'}
                                </div>
                            </td>

                            <!-- Datos del vehículo -->
                            <td>
                                <div class="border rounded p-2 bg-light">
                                    <i class="fas fa-truck text-secondary"></i> <strong>Tracto:</strong><br> ${rutas.camion ? rutas.camion.placa_tracto : 'N/A'}<br>
                                    <i class="fas fa-trailer text-primary"></i> <strong>Carreto:</strong><br> ${rutas.camion ? rutas.camion.placa_carreto : 'N/A'}
                                </div>
                            </td>
                            
                            <!-- Botones de acciones -->
                            <td>
                                <div class="d-flex flex-column gap-1">
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

                           <!-- ESTADO -->
                            <td>
                                ${
                                    (() => {
                                        let estado = (rutas.estado ?? '').toLowerCase().trim();
                                        switch (estado) {
                                            case 'pendiente':
                                                return '<span class="badge bg-success rounded-pill"><i class="fas fa-clock"></i> Pendiente</span>';
                                            case 'cancelado':
                                                return '<span class="badge bg-danger rounded-pill"><i class="fas fa-times-circle"></i> Cancelado</span>';
                                            case 'finalizado':
                                                return '<span class="badge bg-primary rounded-pill"><i class="fas fa-check-circle"></i> Finalizado</span>';
                                            case 'en curso':
                                                return '<span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-spinner fa-spin"></i> En curso</span>';
                                            default:
                                                return '<span class="badge bg-secondary rounded-pill"><i class="fas fa-question-circle"></i> Desconocido</span>';
                                        }
                                    })()
                                }
                            </td>


                            <!-- Costo de viaje -->
                            <td>
                                <div class="border rounded p-2 bg-light">
                                <i class="text-info"></i> <strong>Pago viaje:</strong><br> S/ ${rutas.pago_viaje ?? '0.00'}<br>
                                <i class="text-info"></i> <strong>Caja Chica:</strong><br> S/ ${rutas.caja_chica ?? '0.00'}<br>
                                </div>
                            
                            
                            </td>

                            <!-- Botón de reporte -->
                            <td>
                                <a class="btn btn-primary btn-sm" href="/api/reporte/ruta/${rutas.id}">
                                    <i class="fas fa-file-alt"></i> Reporte
                                </a>
                            </td>

                        </tr>
                    `);
                });
            },
            error: function() {
                alert("Error al obtener datos de rutas.");
            }
        });
    }

    $(document).ready(function() {
        fetchRutas();

        $("#buscador").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            let hasVisibleRow = false;

            if (value === "") {
                fetchRutas();
            } else {
                $("#rutaTableBody tr").filter(function() {
                    const rutaData = $(this).find('td:eq(3)').text().toLowerCase();
                    const isVisible = rutaData.indexOf(value) > -1;

                    $(this).toggle(isVisible);
                    if (isVisible) hasVisibleRow = true;
                });

                if (!hasVisibleRow) {
                    $("#rutaTableBody").html(
                        '<tr><td colspan="7" class="text-center">No se encontraron resultados</td></tr>'
                    );
                }
            }
        });
    });
    
    function eliminar(id) {
        Swal.fire({
            title: 'Eliminar registro',
            text: "¿Está seguro de querer eliminar el registro?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    method: 'DELETE',
                    url:  `/api/rutas/${id}`,
                    headers:{
                         "Authorization": "Bearer " + localStorage.getItem("token")
                    },
                    success: function(){
                        $(`#ruta_${id}`).remove();
                        Swal.fire({
                            icon: 'success',
                            title: 'Ruta eliminada',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    },
                    error: function (){
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al eliminar la ruta.',
                        });
                    }
                });
            }
        });
    }

    // Redirección al detalle de viáticos
    function registrarViaticos(id) {
        window.location.href = `/ruta/${id}/rutaviatico`;
    }

    function registrarPeajes(id) {
        window.location.href = `/ruta/${id}/rutapeaje`;
    }

    function registrarCombustible(id) {
        window.location.href = `/ruta/${id}/rutacombustible`;
    }

    function verReporte(id) {
        window.open(`/rutas/${id}/reporte`, '_blank');
    }
</script>
@endpush
