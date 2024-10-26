@extends('admin.main')

@section('contenido')
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-0">
                                Registro de conductor
                                <button class="btn btn-primary" data-toggle="modal" data-target="#modalRegistroConductor">
                                    <i class="fas fa-file"></i> Nuevo
                                </button>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div>
                                <form action="" method="get">
                                    {{-- <div class="input-group">
                                    <input name="texto" type="text" class="form-control" id="searchText" placeholder="Buscar...">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" id="searchButton">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>                      
                                    </div>
                                </div> --}}

                                    <input class="form-control me-2" type="search" placeholder="Buscar conductor"
                                        aria-label="Search" id="buscador">

                                </form>
                            </div>
                            <div class="mt-2">
                                <div class="table-responsive" >
                                    <table class="table table-striped table-bordered table-hover table-sm" >
                                        <thead>
                                            <tr>
                                                <th width="10%">Opciones</th>
                                                <th width="5%">ID</th>
                                                <th width="15%">Nombre</th>
                                                <th width="15%">Apellidos</th>
                                                <th width="12%">Tipo de Licencia</th>
                                                <th width="10%">Licencia</th>
                                                <th width="10%">Teléfono</th>
                                                <th width="15%">Email</th>
                                                <th width="23%">Dirección</th>
                                            </tr>

                                        </thead>
                                        <tbody id="conductorTableBody">
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

    @include('vista_conductor.registro')
@endsection

@push('scripts')
    <script>
        // busca por cualquier item q este mostrandoce 
        // $(document).ready(function() {
        //     $("#buscador").on("keyup", function() {
        //         var value = $(this).val().toLowerCase();
        //         let hasVisibleRow = false; // Variable para verificar si hay resultados visibles
        //         $("#conductorTableBody tr").filter(function() {
        //             const isVisible = $(this).text().toLowerCase().indexOf(value) > -1;
        //             $(this).toggle(isVisible);
        //             if (isVisible) {
        //                 hasVisibleRow =
        //                     true; // Si hay alguna fila visible, actualizamos la variable
        //             }
        //         });

        //         // Si no hay resultados visibles, mostramos un mensaje
        //         if (!hasVisibleRow) {
        //             $("#conductorTableBody").html(
        //                 '<tr><td colspan="9" class="text-center">No se encontraron resultados</td></tr>'
        //             );
        //         }
        //     });
        // });

        // busca solo por nombre

         $(document).ready(function() {
        $("#buscador").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            let hasVisibleRow = false;

            // Si el buscador está vacío, recargar los conductores completos
            if (value === "") {
                fetchConductores(); // Llamar a la función que carga los conductores
            } else {
                // Filtrar por nombre (columna 3) y por la cuarta columna
                $("#conductorTableBody tr").filter(function() {
                    const nombre = $(this).find('td:eq(2)').text().toLowerCase();
                    const otraColumna = $(this).find('td:eq(3)').text().toLowerCase(); // Agregar la cuarta columna
                    const isVisible = nombre.indexOf(value) > -1 || otraColumna.indexOf(value) > -1; // Buscar en ambas columnas
                    $(this).toggle(isVisible);
                    if (isVisible) {
                        hasVisibleRow = true;
                    }
                });

                // Si no hay resultados visibles, mostrar un mensaje
                if (!hasVisibleRow) {
                    $("#conductorTableBody").html(
                        '<tr><td colspan="9" class="text-center">No se encontraron resultados</td></tr>'
                    );
                }
            }
        });
    });





        // Function to fetch conductores data from API
        function fetchConductores() {
            $.ajax({
                url: "http://127.0.0.1:8000/api/conductores",
                method: "GET",
                success: function(response) {
                    let tbody = $("#conductorTableBody");
                    tbody.empty();
                    $.each(response, function(index, conductor) {
                        tbody.append(`
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-warning btn-sm editar me-2" onclick="editar(${conductor.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-danger btn-sm eliminar" onclick="eliminar(${conductor.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>

                            <td>${conductor.id}</td>
                            <td>${conductor.nombre}</td>
                            <td>${conductor.apellido}</td>
                            <td>${conductor.tipo_licencia}</td>
                            <td>${conductor.licencia}</td>
                            <td>${conductor.telefono}</td>
                            <td>${conductor.email}</td>
                            <td>${conductor.direccion}</td>
                    `);
                    });
                },
                error: function() {
                    alert("Error fetching conductores data.");
                }
            });
        }

        // Load conductores data when page loads
        // $(document).ready(function() {
        //     fetchConductores();

        //     // Search functionality (filtering could also be added to the backend)
        //     $("#searchButton").click(function() {
        //         let searchText = $("#searchText").val();
        //         if (searchText.trim() !== "") {
        //             // Add filtering logic here if needed, for now it will just refresh the table
        //             fetchConductores();
        //         }
        //     });
        // });

        // Example function to handle edit
        // function editar(id) {
        //     alert('Editar conductor ' + id);
        // }


        function eliminar(id) {
            Swal.fire({
                title: 'Eliminar registro',
                text: "¿Esta seguro de querer eliminar el registro?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        method: 'DELETE',
                        url: `http://127.0.0.1:8000/api/conductores/${id}`,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            window.location.reload();
                            Swal.fire({
                                icon: res.status,
                                title: res.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        },
                        error: function(res) {

                        }
                    });

                }
            })

        }
    </script>
@endpush
