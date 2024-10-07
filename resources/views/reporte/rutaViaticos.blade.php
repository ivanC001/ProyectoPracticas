@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">Detalles de la Ruta y Viáticos</h5>
                    </div>
                    <div class="card-body">
                        <!-- Detalles de la Ruta -->
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Total Viáticos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td id="ruta-id"></td>
                                        <td id="ruta-origen"></td>
                                        <td id="ruta-destino"></td>
                                        <td id="ruta-fecha-inicio"></td>
                                        <td id="ruta-fecha-fin"></td>
                                        <td id="ruta-total-viaticos"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Lista de Viáticos -->
                        <h5 class="mt-4">Lista de Viáticos</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre del Servicio</th>
                                        <th>Fecha</th>
                                        <th>Número de Factura</th>
                                        <th>Importe</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody id="viaticosTableBody">
                                    <!-- Aquí se llenarán los datos de los viáticos con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
    // Función para obtener el ID de la ruta directamente desde la URL
    function getRutaIdFromUrl() {
        const pathArray = window.location.pathname.split('/');
        return pathArray[pathArray.length - 1]; // Obtiene el último segmento de la URL
    }

    // Función para obtener datos de viáticos de la ruta desde la API
    function fetchViaticosRuta() {
        const rutaId = getRutaIdFromUrl(); // Llamamos a la función para obtener el ID de la ruta

        if (!rutaId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se encontró el ID de la ruta en la URL. Redirigiendo a la página principal...',
            }).then(() => {
                window.location.href = 'http://127.0.0.1:8000/reporte-ruta'; // Redirigir a la URL específica
            });
            return;
        }

        $.ajax({
            url: `http://127.0.0.1:8000/api/reporte/viaticosRuta/${rutaId}`,
            method: "GET",
            success: function(response) {
                console.log('API response:', response); // Para ver la respuesta completa en la consola

                // Mostrar detalles de la ruta en la tabla principal
                $("#ruta-id").text(response.id);
                $("#ruta-origen").text(response.origen);
                $("#ruta-destino").text(response.destino);
                $("#ruta-fecha-inicio").text(response.fecha_inicio);
                $("#ruta-fecha-fin").text(response.fecha_fin);
                $("#ruta-total-viaticos").text(response.viaticos_sum_importe !== null ? response.viaticos_sum_importe : 'N/A');

                // Llenar la tabla con los viáticos si existen en la respuesta
                let tbody = $("#viaticosTableBody");
                tbody.empty();

                if (response.viaticos && response.viaticos.length > 0) {
                    $.each(response.viaticos, function(index, viatico) {
                        tbody.append(`
                            <tr>
                                <td>${viatico.id}</td>
                                <td>${viatico.nombre_servicio}</td>
                                <td>${viatico.fecha}</td>
                                <td>${viatico.numero_factura}</td>
                                <td>${viatico.importe}</td>
                                <td>${viatico.descripcion}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`<tr><td colspan="6" class="text-center">No se encontraron viáticos para esta ruta.</td></tr>`);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error al obtener datos de viáticos de la ruta:", textStatus, errorThrown);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: jqXHR.responseJSON?.message || 'Hubo un problema al obtener los datos de viáticos. Redirigiendo a la página principal...',
                }).then(() => {
                    window.location.href = 'http://127.0.0.1:8000/reporte-ruta'; // Redirigir a la URL específica
                });
            }
        });
    }

    // Cargar datos de viáticos al cargar la página
    $(document).ready(function() {
        fetchViaticosRuta();
    });
</script>
@endpush
