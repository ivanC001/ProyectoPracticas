@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">
                            Registro camiones 
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalRegistroCamion">
                                <i class="fas fa-file"></i> Nuevo
                            </button>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <form action="" method="get">
                                <div class="input-group">
                                    <input name="texto" type="text" class="form-control" id="searchText" placeholder="Buscar...">
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
                                            <th width="15%">Fecha ingreso</th>
                                            <th width="15%">Placa tracto</th>
                                            <th width="15%">Placa carreto</th>
                                            <th width="10%">Color</th>
                                            <th width="10%">MTC</th>
                                            <th width="10%">Foto camino</th>
                                        </tr>
                                    </thead>
                                    <tbody id="camionTableBody">
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

@include('camion.registro')

@endsection

@push('scripts')
<script>
    // Function to fetch camiones data from API
    function fetchCamiones() {
        $.ajax({
            url: "http://127.0.0.1:8000/api/camiones",
            method: "GET",
            success: function(response) {
                let tbody = $("#camionTableBody");
                tbody.empty(); 
                $.each(response, function(index, camion) {
                    tbody.append(`
                        <tr>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm editar" onclick="editar(${camion.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button type="button" class="btn btn-danger btn-sm eliminar" onclick="eliminar(${camion.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                            <td>${camion.id}</td>
                            <td>${camion.fecha_ingreso}</td>
                            <td>${camion.placa_tracto}</td>
                            <td>${camion.placa_carreto}</td>
                            <td>${camion.color}</td>
                            <td>${camion.mtc}</td>
                            <td>${camion.foto_camino}</td>
                        </tr>
                    `);
                });
            },
            error: function() {
                alert("Error fetching Camion data.");
            }
        });
    }

    // Load camiones data when page loads
    $(document).ready(function() {
        fetchCamiones();

        // Search functionality (filtering could also be added to the backend)
        $("#searchButton").click(function() {
            let searchText = $("#searchText").val();
            if (searchText.trim() !== "") {
                // Add filtering logic here if needed, for now it will just refresh the table
                fetchCamiones();
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
                    url: `http://127.0.0.1:8000/api/camiones/${id}`,
                    headers:{
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
                    error: function(res){
                        alert("Error al eliminar el camión.");
                    }
                });
            }
        });
    }
</script>
@endpush
