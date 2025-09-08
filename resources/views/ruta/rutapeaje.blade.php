@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <h4 class="text-center mb-4">Detalle de Ruta y Peajes</h4>

        <!-- Información de la ruta -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <strong>Información de la Ruta</strong>
            </div>
            <div class="card-body" id="ruta-info">
                <p class="text-center">Cargando datos...</p>
            </div>
        </div>

        <!-- Lista de registros de peajes -->
        <div class="card">
            <div class="card-header bg-warning text-dark d-flex justify-content-between">
                <strong>Registro de Peajes</strong>
                <button class="btn btn-light btn-sm" id="btn-nuevo-peaje">+ Nuevo</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Importe (S/)</th>
                            <th>Fecha y Hora</th>
                            <th>Comprobante</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="peaje-list">
                        <tr><td colspan="6">Cargando registros de peajes...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para registrar/editar peaje -->
<div class="modal fade" id="peajeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="peajeForm">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Registrar Peaje</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="peajeId">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Nombre</label>
              <input type="text" id="nombre" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>Importe (S/)</label>
              <input type="number" step="0.01" id="importe" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>Fecha y Hora</label>
              <input type="datetime-local" id="fecha_hora" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>Comprobante</label>
              <input type="text" id="comprobante" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    const rutaId = window.location.pathname.split('/')[2]; 
    let apiPeajeUrl = `http://127.0.0.1:8000/api/rutas/${rutaId}/peajes`;

    // Cargar información de la ruta
    function fetchRuta() {
        $.get(`http://127.0.0.1:8000/api/rutas/${rutaId}`, function(response) {
            $('#ruta-info').html(`
                <p><strong>Origen:</strong> ${response.origen}</p>
                <p><strong>Destino:</strong> ${response.destino}</p>
                <p><strong>Conductor:</strong> ${response.conductor?.nombre ?? 'N/A'}</p>
                <p><strong>Vehículo:</strong> ${response.camion?.placa_tracto ?? 'N/A'} / ${response.camion?.placa_carreto ?? 'N/A'}</p>
                <p><strong>Fechas:</strong> ${response.fecha_inicio} - ${response.fecha_fin}</p>
            `);
        }).fail(() => {
            $('#ruta-info').html('<p class="text-danger">Error al cargar los datos de la ruta.</p>');
        });
    }

    // Cargar peajes de la ruta
    function fetchPeajes() {
        $.get(apiPeajeUrl, function(response) {
            let rows = '';
            if(response.length > 0){
                response.forEach((p, index) => {
                    rows += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${p.nombre ?? '-'}</td>
                            <td>${p.importe ?? '-'}</td>
                            <td>${p.fecha_hora ?? '-'}</td>
                            <td>${p.comprobante ?? '-'}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editPeaje(${p.id})">Editar</button>
                                <button class="btn btn-danger btn-sm" onclick="deletePeaje(${p.id})">Eliminar</button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                rows = '<tr><td colspan="6">No hay registros de peajes para esta ruta.</td></tr>';
            }
            $('#peaje-list').html(rows);
        }).fail(() => {
            $('#peaje-list').html('<tr><td colspan="6" class="text-danger">Error al cargar peajes.</td></tr>');
        });
    }

    // Guardar peaje (nuevo o edición)
    $('#peajeForm').on('submit', function(e){
        e.preventDefault();

        let id = $('#peajeId').val();
        let data = {
            nombre: $('#nombre').val(),
            importe: $('#importe').val(),
            fecha_hora: $('#fecha_hora').val(),
            comprobante: $('#comprobante').val()
        };

        if(id){ // editar
            $.ajax({
                url: `${apiPeajeUrl}/${id}`,
                method: 'PUT',
                data: data,
                success: function(){
                    $('#peajeModal').modal('hide');
                    fetchPeajes();
                }
            });
        } else { // nuevo
            $.post(apiPeajeUrl, data, function(){
                $('#peajeModal').modal('hide');
                fetchPeajes();
            });
        }
    });

    // Editar peaje
    function editPeaje(id){
        $.get(`${apiPeajeUrl}/${id}`, function(p){
            $('#peajeId').val(p.id);
            $('#nombre').val(p.nombre);
            $('#importe').val(p.importe);
            $('#fecha_hora').val(p.fecha_hora);
            $('#comprobante').val(p.comprobante);

            $('#peajeModal').modal('show');
        });
    }

    // Eliminar peaje
    function deletePeaje(id){
        if(confirm("¿Seguro que deseas eliminar este peaje?")){
            $.ajax({
                url: `${apiPeajeUrl}/${id}`,
                method: 'DELETE',
                success: function(){
                    fetchPeajes();
                }
            });
        }
    }

    // Abrir modal nuevo
    $('#btn-nuevo-peaje').click(() => {
        $('#peajeForm')[0].reset();
        $('#peajeId').val('');
        $('#peajeModal').modal('show');
    });

    $(document).ready(function(){
        fetchRuta();
        fetchPeajes();
    });
</script>
@endpush
