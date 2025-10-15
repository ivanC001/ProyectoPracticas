@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <h4 class="text-center mb-4">Detalle de Ruta y Viáticos</h4>

        <!-- Información de la ruta -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <strong>Información de la Ruta</strong>
            </div>
            <div class="card-body" id="ruta-info">
                <p class="text-center">Cargando datos...</p>
            </div>
        </div>

        <!-- Tabla de Viáticos -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <strong>Viáticos Registrados</strong>
                <button class="btn btn-light btn-sm" onclick="openViaticoModal()">+ Agregar Viático</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Fecha</th>
                            <th>Factura</th>
                            <th>Importe</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="viaticos-list">
                        <tr><td colspan="6">Cargando viáticos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    
        </div>
    </div>
</div>

<!-- Modal Viático -->
<div class="modal fade" id="viaticoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="viaticoModalTitle">Agregar Viático</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="viaticoForm">
                    <input type="hidden" name="id" id="viaticoId">
                    <div class="mb-3">
                        <label>Nombre Servicio</label>
                        <input type="text" name="nombre_servicio" id="viaticoNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" id="viaticoFecha" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Número de Factura</label>
                        <input type="text" name="numero_factura" id="viaticoFactura" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Importe</label>
                        <input type="number" step="0.01" name="importe" id="viaticoImporte" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <textarea name="descripcion" id="viaticoDescripcion" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const rutaId = window.location.pathname.split('/')[2];
    const apiUrl = '/api';
    let currentRuta = null;

    // Cargar datos iniciales
    function fetchRuta() {
        $.get(`${apiUrl}/rutasViaticos/${rutaId}`, function(response) {
            currentRuta = response;

            $('#ruta-info').html(`
                <p><strong>Origen:</strong> ${response.origen}</p>
                <p><strong>Destino:</strong> ${response.destino}</p>
                <p><strong>Conductor:</strong> ${response.conductor?.nombre ?? 'N/A'}</p>
                <p><strong>Vehículo:</strong> ${response.camion?.placa_tracto ?? 'N/A'} / ${response.camion?.placa_carreto ?? 'N/A'}</p>
                <p><strong>Fechas:</strong> ${response.fecha_inicio} - ${response.fecha_fin}</p>
            `);

            renderViaticos(response.viaticos);
            renderCombustibles(response.combustibles);
        });
    }

    // Renderizar viáticos
    function renderViaticos(viaticos) {
        let rows = viaticos.length
            ? viaticos.map((v, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>${v.nombre_servicio}</td>
                    <td>${v.fecha}</td>
                    <td>${v.numero_factura}</td>
                    <td>S/. ${v.importe}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick='editViatico(${JSON.stringify(v)})'>Editar</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteViatico(${v.id})">Eliminar</button>
                    </td>
                </tr>
            `).join('')
            : '<tr><td colspan="6">No hay viáticos registrados.</td></tr>';

        $('#viaticos-list').html(rows);
    }

    

    // Abrir modal para agregar o editar viático
    function openViaticoModal() {
        $('#viaticoForm')[0].reset();
        $('#viaticoId').val('');
        $('#viaticoModalTitle').text('Agregar Viático');
        new bootstrap.Modal(document.getElementById('viaticoModal')).show();
    }

    function editViatico(viatico) {
        $('#viaticoId').val(viatico.id);
        $('#viaticoNombre').val(viatico.nombre_servicio);
        $('#viaticoFecha').val(viatico.fecha);
        $('#viaticoFactura').val(viatico.numero_factura);
        $('#viaticoImporte').val(viatico.importe);
        $('#viaticoDescripcion').val(viatico.descripcion);
        $('#viaticoModalTitle').text('Editar Viático');
        new bootstrap.Modal(document.getElementById('viaticoModal')).show();
    }

    // Guardar viático
    $('#viaticoForm').submit(function(e) {
        e.preventDefault();
        const viatico = {
            id: $('#viaticoId').val() || null,
            nombre_servicio: $('#viaticoNombre').val(),
            fecha: $('#viaticoFecha').val(),
            numero_factura: $('#viaticoFactura').val(),
            importe: $('#viaticoImporte').val(),
            descripcion: $('#viaticoDescripcion').val()
        };
        updateRuta({ viaticos: [viatico] });
        bootstrap.Modal.getInstance(document.getElementById('viaticoModal')).hide();
    });

   
    // Update vía API
    function updateRuta(data) {
        $.ajax({
            url: `${apiUrl}/rutasViaticos/${rutaId}`,
            method: 'PUT',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function() {
                fetchRuta();
                alert('Datos guardados correctamente.');
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('Error al guardar datos.');
            }
        });
    }

    // Eliminar viático
    function deleteViatico(id) {
        if (!confirm('¿Seguro que deseas eliminar este viático?')) return;
        $.ajax({
            url: `${apiUrl}/viaticos/${id}`,
            method: 'DELETE',
            success: function() {
                fetchRuta();
                alert('Viático eliminado correctamente.');
            },
            error: function() {
                alert('Error al eliminar el viático.');
            }
        });
    }



    $(document).ready(fetchRuta);
</script>
@endpush
