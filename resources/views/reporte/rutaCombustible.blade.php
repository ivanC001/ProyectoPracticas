@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">Detalles de la Ruta y Combustible</h5>
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
                                        <th>Total Combustible</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td id="ruta-id"></td>
                                        <td id="ruta-origen"></td>
                                        <td id="ruta-destino"></td>
                                        <td id="ruta-fecha-inicio"></td>
                                        <td id="ruta-fecha-fin"></td>
                                        <td id="ruta-total-combustible"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Lista de Combustible -->
                        <h5 class="mt-4">Lista de Combustible</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Número de Factura</th>
                                        <th>Grifo</th>
                                        <th>Fecha y Hora</th>
                                        <th>Galones</th>
                                        <th>Importe</th>
                                        <th>Kilometraje Inicial</th>
                                        <th>Kilometraje Final</th>
                                        <th>Tipo de Combustible</th>
                                    </tr>
                                </thead>
                                <tbody id="combustibleTableBody">
                                    <!-- Aquí se llenarán los datos de combustible con JavaScript -->
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

    // Función para obtener datos de combustible de la ruta desde la API
    function fetchCombustibleRuta() {
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
            url: `http://127.0.0.1:8000/api/reporte/combustibleRuta/${rutaId}`,
            method: "GET",
            success: function(response) {
                console.log('API response:', response); // Para ver la respuesta completa en la consola

                // Mostrar detalles de la ruta en la tabla principal
                $("#ruta-id").text(response.id);
                $("#ruta-origen").text(response.origen);
                $("#ruta-destino").text(response.destino);
                $("#ruta-fecha-inicio").text(response.fecha_inicio);
                $("#ruta-fecha-fin").text(response.fecha_fin);
                $("#ruta-total-combustible").text(response.combustibles_sum_importe !== null ? response.combustibles_sum_importe : 'N/A');

                // Llenar la tabla con los datos de combustible si existen en la respuesta
                let tbody = $("#combustibleTableBody");
                tbody.empty();

                if (response.combustibles && response.combustibles.length > 0) {
                    $.each(response.combustibles, function(index, combustible) {
                        tbody.append(`
                            <tr>
                                <td>${combustible.id}</td>
                                <td>${combustible.num_factura}</td>
                                <td>${combustible.grifo}</td>
                                <td>${combustible.fecha_hora}</td>
                                <td>${combustible.galonesCombustible}</td>
                                <td>${combustible.importe}</td>
                                <td>${combustible.kilometraje_inicial}</td>
                                <td>${combustible.kilometraje_final}</td>
                                <td>${combustible.tipo_combustible}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`<tr><td colspan="9" class="text-center">No se encontraron registros de combustible para esta ruta.</td></tr>`);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error al obtener datos de combustible de la ruta:", textStatus, errorThrown);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: jqXHR.responseJSON?.message || 'Hubo un problema al obtener los datos de combustible. Redirigiendo a la página principal...',
                }).then(() => {
                    window.location.href = 'http://127.0.0.1:8000/reporte-ruta'; // Redirigir a la URL específica
                });
            }
        });
    }

    // Cargar datos de combustible al cargar la página
    $(document).ready(function() {
        fetchCombustibleRuta();
    });
</script>
@endpush
