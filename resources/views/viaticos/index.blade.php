@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">
                            Registro de viáticos 
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalRegistroViatico">
                                <i class="fas fa-file"></i> Nuevo Viático
                            </button>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <form action="" method="get">
                                <div class="input-group">
                                    <input name="texto" type="text" class="form-control" id="searchText" placeholder="Buscar viático...">
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
                                            <th width="15%">Nombre</th> <!-- Mostrar ID, salida y llegada -->
                                            <th width="15%">Ruta</th> <!-- Mostrar ID, salida y llegada -->
                                            <th width="10%">Fecha Viático</th>
                                            <th width="10%">Monto</th>
                                            <th width="15%">Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viaticoTableBody">
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

@include('viaticos.registro') {{-- Modal para registrar nuevo viático --}}

@endsection

@push('scripts')
<script>
    // Función para obtener datos de viáticos desde la API
    function fetchViaticos() {
        $.ajax({
            url: "/api/viaticos", // Ruta API para obtener viáticos
            method: "GET",
            success: function(response) {
                console.log(response); // Ver respuesta en la consola

                let tbody = $("#viaticoTableBody");
                tbody.empty(); 
                
                // Asegúrate de que 'response' sea un array con viáticos
                $.each(response, function(index, viatico) {
                    let rutaInfo = viatico.ruta 
                        ? `${viatico.ruta.id} - desde ${viatico.ruta.origen} a ${viatico.ruta.destino}` 
                        : 'N/A';

                    tbody.append(`
                        <tr>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm editar" onclick="editarViatico(${viatico.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button type="button" class="btn btn-danger btn-sm eliminar" onclick="eliminar(${viatico.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                            <td>${viatico.id}</td>
                            <td>${viatico.nombre_servicio}</td>
                            <td>${rutaInfo}</td> <!-- Muestra el ID de la ruta, salida y llegada -->
                            <td>${viatico.fecha}</td>
                            <td>${viatico.importe}</td>
                            <td>${viatico.descripcion}</td>
                        </tr>
                    `);
                });
            },
            error: function() {
                alert("Error al obtener datos de viáticos.");
            }
        });
    }

    // Cargar datos de viáticos al cargar la página
    $(document).ready(function() {
        fetchViaticos();

        // Funcionalidad de búsqueda
        $("#searchButton").click(function() {
            let searchText = $("#searchText").val();
            if (searchText.trim() !== "") {
                fetchViaticos();
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
                    url:  `/api/viaticos/${id}`,
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
