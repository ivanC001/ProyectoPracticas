@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">
                            Reporte de Rutas y Consumos
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
                                            <th>ID</th>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Total Viáticos</th>
                                            <th>Total Combustible</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rutaTableBody">
                                        {{-- Aquí se llenará dinámicamente la tabla con JavaScript --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="pagination-wrapper">
                            <ul class="pagination justify-content-center" id="pagination">
                                {{-- Aquí se llenará dinámicamente la paginación --}}
                            </ul>
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
    // Función para obtener datos de rutas y consumos desde la API
    function fetchRutas(page = 1) {
        $.ajax({
            url: `http://127.0.0.1:8000/api/reporte/rutas-consumos?page=${page}`,
            method: "GET",
            success: function(response) {
                console.log(response); // Para ver la respuesta

                let tbody = $("#rutaTableBody");
                tbody.empty();

                // Recorrer las rutas y llenar la tabla
                $.each(response.data, function(index, ruta) {
                    tbody.append(`
                        <tr>
                            <td>${ruta.id}</td>
                            <td>${ruta.origen}</td>
                            <td>${ruta.destino}</td>
                            <td>${ruta.fecha_inicio}</td>
                            <td>${ruta.fecha_fin}</td>
                            <td>
                                ${ruta.total_viaticos !== null ? ruta.total_viaticos : 'N/A'}
                                <button class="btn btn-info btn-sm ml-2" onclick="verDetallesViaticos(${ruta.id})">Ver detalles</button>
                            </td>
                            <td>
                                ${ruta.total_combustible !== null ? ruta.total_combustible : 'N/A'}
                                <button class="btn btn-info btn-sm ml-2" onclick="verDetallesCombustible(${ruta.id})">Ver detalles</button>
                            </td>
                        </tr>
                    `);
                });

                // Llenar la paginación
                let pagination = $("#pagination");
                pagination.empty();

                // Botón "Anterior"
                if (response.prev_page_url) {
                    pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="javascript:void(0);" onclick="fetchRutas(${page - 1})">&laquo; Anterior</a>
                        </li>
                    `);
                }

                // Botones de páginas numeradas
                $.each(response.links, function(index, link) {
                    if (link.url) {
                        pagination.append(`
                            <li class="page-item ${link.active ? 'active' : ''}">
                                <a class="page-link" href="javascript:void(0);" onclick="fetchRutas(${link.label})">${link.label}</a>
                            </li>
                        `);
                    }
                });

                // Botón "Siguiente"
                if (response.next_page_url) {
                    pagination.append(`
                        <li class="page-item">
                            <a class="page-link" href="javascript:void(0);" onclick="fetchRutas(${page + 1})">Siguiente &raquo;</a>
                        </li>
                    `);
                }
            },
            error: function() {
                alert("Error al obtener datos de rutas.");
            }
        });
    }

    // Función para redireccionar a la página de detalles de viáticos
    function verDetallesViaticos(rutaId) {
        window.location.href = `http://127.0.0.1:8000/reporte/ruta-viaticos/${rutaId}`;
    }

    // Función para redireccionar a la página de detalles de combustible
    function verDetallesCombustible(rutaId) {
        window.location.href = `http://127.0.0.1:8000/reporte/ruta-combustible/${rutaId}`;
    }

    // Cargar datos de rutas al cargar la página
    $(document).ready(function() {
        fetchRutas();
    });
</script>
@endpush
