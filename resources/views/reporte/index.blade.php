@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">Reporte de Rutas y Consumos</h5>
                    </div>
                    <div class="card-body">
                        <!-- Filtros de Fecha -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="fechaInicio">Fecha Inicio:</label>
                                <input type="date" class="form-control" id="fechaInicio">
                            </div>
                            <div class="col-md-4">
                                <label for="fechaFin">Fecha Fin:</label>
                                <input type="date" class="form-control" id="fechaFin">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-primary" onclick="aplicarFiltros()">Ver</button>
                            </div>
                        </div>

                        <!-- Botón para exportar reporte completo (solo visible cuando no hay filtros) -->
                        <div id="exportarReporteCompleto" class="mb-3">
                            <button class="btn btn-success" onclick="exportarReporteCompleto()">Exportar Reporte Completo</button>
                        </div>

                        <!-- Tabla de Rutas y Consumos -->
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
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="rutaTableBody">
                                    {{-- Aquí se llenará dinámicamente la tabla con JavaScript --}}
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
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
    // Función para obtener y mostrar rutas y consumos desde la API
    function fetchRutas(page = 1) {
        const apiUrl = `http://127.0.0.1:8000/api/reporte/rutas-consumos?page=${page}`;
        $.ajax({
            url: apiUrl,
            method: "GET",
            success: function(response) {
                actualizarTabla(response);
                $('#exportarReporteCompleto').show(); // Mostrar el botón de exportar cuando no hay filtros
            },
            error: function() {
                alert("Error al obtener datos de rutas.");
            }
        });
    }

    // Función para aplicar filtros de fecha y actualizar la tabla
    function aplicarFiltros() {
        const fechaInicio = $('#fechaInicio').val();
        const fechaFin = $('#fechaFin').val();

        let apiUrl = 'http://127.0.0.1:8000/api/reporte/rutas-consumos';

        // Verificar si se selecciona solo la fecha de inicio o un rango completo
        if (fechaInicio && !fechaFin) {
            apiUrl += `?fecha_inicio=${fechaInicio}`;
        } else if (fechaInicio && fechaFin) {
            apiUrl += `?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
        }

        // Hacer la solicitud a la API con el filtro correspondiente
        $.ajax({
            url: apiUrl,
            method: "GET",
            success: function(response) {
                actualizarTabla(response);
                $('#exportarReporteCompleto').hide(); // Ocultar el botón de exportar cuando se aplica el filtro
            },
            error: function() {
                alert("Error al aplicar los filtros.");
            }
        });
    }

    // Función para actualizar la tabla con los datos de la respuesta
    function actualizarTabla(response) {
        let tbody = $("#rutaTableBody");
        tbody.empty();

        $.each(response.data, function(index, ruta) {
            tbody.append(`
                <tr>
                    <td>${ruta.id}</td>
                    <td>${ruta.origen}</td>
                    <td>${ruta.destino}</td>
                    <td>${ruta.fecha_inicio}</td>
                    <td>${ruta.fecha_fin}</td>
                    <td>${ruta.total_viaticos !== null ? ruta.total_viaticos : 'N/A'}</td>
                    <td>${ruta.total_combustible !== null ? ruta.total_combustible : 'N/A'}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="verDetallesViaticos(${ruta.id})">Ver Viáticos</button>
                        <button class="btn btn-info btn-sm" onclick="verDetallesCombustible(${ruta.id})">Ver Combustible</button>
                        <button class="btn btn-warning btn-sm" onclick="generarReporte(${ruta.id})">Generar Reporte</button>
                    </td>
                </tr>
            `);
        });

        // Manejar paginación
        actualizarPaginacion(response);
    }

    // Función para redireccionar a la página de detalles de viáticos
    function verDetallesViaticos(rutaId) {
        window.location.href = `http://127.0.0.1:8000/reporte/ruta-viaticos/${rutaId}`;
    }

    // Función para redireccionar a la página de detalles de combustible
    function verDetallesCombustible(rutaId) {
        window.location.href = `http://127.0.0.1:8000/reporte/ruta-combustible/${rutaId}`;
    }

    // Función para generar un reporte individual para una ruta específica
    function generarReporte(rutaId) {
        window.location.href = `http://127.0.0.1:8000/api/reporte/rutas-consumos?id=${rutaId}&exportar=1`;
    }

    // Función para exportar el reporte completo cuando no hay filtros aplicados
    function exportarReporteCompleto() {
        window.location.href = 'http://127.0.0.1:8000/api/reporte/rutas-consumos?exportar=1';
    }

    // Función para manejar la actualización de la paginación
    function actualizarPaginacion(response) {
        let pagination = $("#pagination");
        pagination.empty();

        // Botón "Anterior"
        if (response.prev_page_url) {
            pagination.append(`
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0);" onclick="fetchRutas(${response.current_page - 1})">&laquo; Anterior</a>
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
                    <a class="page-link" href="javascript:void(0);" onclick="fetchRutas(${response.current_page + 1})">Siguiente &raquo;</a>
                </li>
            `);
        }
    }

    // Cargar datos de rutas al cargar la página
    $(document).ready(function() {
        fetchRutas();
    });
</script>
@endpush
