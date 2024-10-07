@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">
                            Detalles de la Ruta y Viáticos
                        </h5>
                    </div>
                    <div class="card-body">

                        <!-- Select para cambiar entre rutas -->
                        <div class="form-group">
                            <label for="rutaSelect">Seleccionar Ruta:</label>
                            <select id="rutaSelect" class="form-control">
                                <!-- Aquí se llenarán las opciones de rutas con JavaScript -->
                            </select>
                        </div>

                        <!-- Tabla para los datos de la Ruta -->
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

                        <!-- Tabla para los Viáticos de la Ruta -->
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

                        <!-- Tabla para los Combustibles de la Ruta -->
                        <h5 class="mt-4">Lista de Combustibles</h5>
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
                                <tbody id="combustiblesTableBody">
                                    <!-- Aquí se llenarán los datos de combustibles con JavaScript -->
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
// Función para hacer una petición a la API
function fetchRutaData(rutaId) {
    fetch(`http://127.0.0.1:8000/api/reporte/completoRuta/${rutaId}`)
        .then(response => response.json())
        .then(data => {
            fillRutaData(data);
        })
        .catch(error => console.error('Error fetching ruta data:', error));
}

// Función para llenar los datos de la ruta y viáticos
function fillRutaData(data) {
    let ruta = data.viaticos;
    let combustibles = data.combustibles;

    // Llenar los datos de la ruta
    document.getElementById('ruta-id').textContent = ruta.id;
    document.getElementById('ruta-origen').textContent = ruta.origen;
    document.getElementById('ruta-destino').textContent = ruta.destino;
    document.getElementById('ruta-fecha-inicio').textContent = ruta.fecha_inicio;
    document.getElementById('ruta-fecha-fin').textContent = ruta.fecha_fin;
    document.getElementById('ruta-total-viaticos').textContent = ruta.viaticos_sum_importe !== null ? ruta.viaticos_sum_importe : 'N/A';

    // Llenar la tabla de viáticos
    fillViaticosData(ruta.viaticos);
    
    // Llenar la tabla de combustibles
    fillCombustiblesData(combustibles.combustibles);
}

// Función para llenar los viáticos en la tabla
function fillViaticosData(viaticos) {
    let tbody = document.getElementById('viaticosTableBody');
    tbody.innerHTML = ''; // Limpiar la tabla antes de llenarla

    viaticos.forEach(viatico => {
        let row = `
            <tr>
                <td>${viatico.id}</td>
                <td>${viatico.nombre_servicio}</td>
                <td>${viatico.fecha !== '0000-00-00' ? viatico.fecha : 'N/A'}</td>
                <td>${viatico.numero_factura}</td>
                <td>${viatico.importe}</td>
                <td>${viatico.descripcion !== 'nuull' ? viatico.descripcion : 'N/A'}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

// Función para llenar los combustibles en la tabla
function fillCombustiblesData(combustibles) {
    let tbody = document.getElementById('combustiblesTableBody');
    tbody.innerHTML = ''; // Limpiar la tabla antes de llenarla

    combustibles.forEach(combustible => {
        let row = `
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
        `;
        tbody.innerHTML += row;
    });
}

// Cargar los datos al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Llamada a la API para obtener los datos de la ruta por defecto (ID 1)
    fetchRutaData(1);
    
    // Cambiar la ruta cuando se selecciona otra en el select
    document.getElementById('rutaSelect').addEventListener('change', function() {
        let rutaId = this.value;
        fetchRutaData(rutaId); // Actualizar con la ruta seleccionada
    });
});
</script>
@endpush
