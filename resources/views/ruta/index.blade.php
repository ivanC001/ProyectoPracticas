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
                                <div class="input-group">
                                    <input name="texto" type="text" class="form-control" id="searchText" placeholder="Buscar ruta...">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" id="searchButton">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>                      
                                    </div>
                                </div>
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
                                            <th width="10%">Fecha LLegada</th>
                                            <th width="10%">Origen</th>
                                            <th width="10%">Destino</th>
                                            <th width="10%">Conductor</th>
                                            <th width="10%">Placa Tracto</th> <!-- Nueva columna -->
                                            <th width="10%">Placa Carreto</th> <!-- Nueva columna -->
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
                console.log(response); // <-- Agrega esto para revisar la respuesta

                let tbody = $("#rutaTableBody");
                tbody.empty(); 
                
                // Asegúrate de que 'response' sea un array con rutas
                $.each(response, function(index, rutas) {
                    tbody.append(`
                        <tr>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm editar" onclick="editar(${rutas.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button type="button" class="btn btn-danger btn-sm eliminar" onclick="eliminar(${rutas.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                            <td>${rutas.id}</td>
                            <td>${rutas.fecha_inicio}</td>
                            <td>${rutas.fecha_fin}</td>
                            <td>${rutas.origen}</td>
                            <td>${rutas.destino}</td>
                            <td>${rutas.conductor ? rutas.conductor.nombre : 'N/A'}</td>  <!-- Verifica si el conductor existe -->
                            <td>${rutas.camion ? rutas.camion.placa_tracto : 'N/A'}</td> <!-- Verifica si el camión existe -->
                            <td>${rutas.camion ? rutas.camion.placa_carreto : 'N/A'}</td> <!-- Nueva columna para placa carreto -->
                        </tr>
                    `);
                });
            },
            error: function() {
                alert("Error al obtener datos de rutas.");
            }
        });
    }

    // Cargar datos de rutas al cargar la página
    $(document).ready(function() {
        fetchRutas();

        // Funcionalidad de búsqueda (se podría añadir filtrado al backend)
        $("#searchButton").click(function() {
            let searchText = $("#searchText").val();
            if (searchText.trim() !== "") {
                // Agrega la lógica de filtrado aquí si es necesario, por ahora solo actualizará la tabla
                fetchRutas();
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
                    url:  `http://127.0.0.1:8000/api/rutas/${id}`,
                    headers:{
                      'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res){
                        window.location.reload();
                        Swal.fire({
                            icon: res.status,
                            title: res.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    },
                    error: function (res){
                        console.log(res);
                    }
                });
            }
        });
    }
</script>
@endpush
