@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">
                            Registro de rutas 
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalRegistroRuta">
                                <i class="fas fa-file"></i> Nueva Ruta
                            </button>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <form action="" method="get">
                                <input class="form-control me-2" type="search" placeholder="Buscar Ruta por origen o destino..."
                                    aria-label="Search" id="buscador">
                            </form>
                        </div>
                        <div class="mt-2">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th width="10%">Opciones</th>
                                            <th width="5%">ID</th>
                                            <th width="10%">Fecha Salida</th>
                                            <th width="10%">Fecha Llegada</th>
                                            <th width="10%">Origen</th>
                                            <th width="10%">Destino</th>
                                            <th width="10%">Conductor</th>
                                            <th width="10%">Placa Tracto</th>
                                            <th width="10%">Placa Carreto</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rutaTableBody">
                                        {{-- Aquí se llenará dinámicamente la tabla con JavaScript --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('ruta.registro') {{-- Modal para registrar nueva ruta --}}

@endsection

@push('scripts')
<script>
    // Función para obtener datos de rutas desde la API
    function fetchRutas() {
        $.ajax({
            url: "http://127.0.0.1:8000/api/rutas",
            method: "GET",
            success: function(response) {
                let tbody = $("#rutaTableBody");
                tbody.empty(); 

                // Invertir el orden de las rutas para mostrar del último ID al primero
                let rutasInvertidas = response.reverse(); 

                // Asegúrate de que 'response' sea un array con rutas
                $.each(rutasInvertidas, function(index, rutas) {
                    tbody.append(`
                        <tr id="ruta_${rutas.id}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-warning btn-sm editar me-2" onclick="editar(${rutas.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-danger btn-sm eliminar" onclick="eliminar(${rutas.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                            <td>${rutas.id}</td>
                            <td>${rutas.fecha_inicio}</td>
                            <td>${rutas.fecha_fin}</td>
                            <td>${rutas.origen}</td>
                            <td>${rutas.destino}</td>
                            <td>${rutas.conductor ? rutas.conductor.nombre : 'N/A'}</td>
                            <td>${rutas.camion ? rutas.camion.placa_tracto : 'N/A'}</td>
                            <td>${rutas.camion ? rutas.camion.placa_carreto : 'N/A'}</td>
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
        fetchRutas(); // Cargar las rutas al cargar la página

        // Implementación del buscador para buscar por Origen o Destino
        $("#buscador").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            let hasVisibleRow = false;

            // Si el buscador está vacío, recargar las rutas completas
            if (value === "") {
                fetchRutas(); 
            } else {
                // Filtrar por origen y destino
                $("#rutaTableBody tr").filter(function() {
                    const origen = $(this).find('td:eq(4)').text().toLowerCase();
                    const destino = $(this).find('td:eq(5)').text().toLowerCase();
                    const isVisible = origen.indexOf(value) > -1 || destino.indexOf(value) > -1;

                    $(this).toggle(isVisible);
                    if (isVisible) {
                        hasVisibleRow = true;
                    }
                });

                // Si no hay resultados visibles, mostrar un mensaje
                if (!hasVisibleRow) {
                    $("#rutaTableBody").html(
                        '<tr><td colspan="9" class="text-center">No se encontraron resultados</td></tr>'
                    );
                }
            }
        });
    });

    // Función para eliminar una ruta sin recargar la página
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
                    url:  `http://127.0.0.1:8000/api/rutas/${id}`,
                    headers:{
                        'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res){
                        // Eliminar la fila sin recargar la página
                        $(`#ruta_${id}`).remove();
                        Swal.fire({
                            icon: 'success',
                            title: 'Ruta eliminada',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    },
                    error: function (res){
                        console.log(res);
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
</script>
@endpush
