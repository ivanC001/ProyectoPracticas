@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <h4 class="text-center mb-4">Detalle de Ruta y Combustible</h4>

        <!-- Información de la ruta -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <strong>Información de la Ruta</strong>
            </div>
            <div class="card-body" id="ruta-info">
                <p class="text-center">Cargando datos...</p>
            </div>
        </div>

        <!-- Lista de registros de combustible -->
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between">
                <strong>Registro de Combustible</strong>
                <button class="btn btn-light btn-sm" id="btn-nuevo">+ Nuevo</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>N° Factura</th>
                            <th>Grifo</th>
                            <th>Fecha y Hora</th>
                            <th>Galones</th>
                            <th>Importe (S/)</th>
                            <th>Kilometraje Inicial</th>
                            <th>Kilometraje Final</th>
                            <th>Tipo Combustible</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="combustible-list">
                        <tr><td colspan="10">Cargando registros de combustible...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para registrar/editar combustible -->
<div class="modal fade" id="combustibleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="combustibleForm">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Registrar Combustible</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="combustibleId">
          <input type="hidden" id="ruta_id"> <!-- oculto para enviar la ruta -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>N° Factura</label>
              <input type="text" id="num_factura" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Grifo</label>
              <input type="text" id="grifo" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Fecha y Hora</label>
              <input type="datetime-local" id="fecha_hora" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Galones</label>
              <input type="number" step="0.01" id="galonesCombustible" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Importe (S/)</label>
              <input type="number" step="0.01" id="importe" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Kilometraje Inicial</label>
              <input type="number" id="kilometraje_inicial" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Kilometraje Final</label>
              <input type="number" id="kilometraje_final" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label>Tipo Combustible</label>
              <input type="text" id="tipo_combustible" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar</button>
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
    let apiUrl = `http://127.0.0.1:8000/api/rutas/${rutaId}/combustibles`;

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

    // Cargar combustibles de la ruta
    function fetchCombustible() {
        $.get(apiUrl, function(response) {
            let rows = '';
            if(response.length > 0){
                response.forEach((c, index) => {
                    rows += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${c.num_factura ?? '-'}</td>
                            <td>${c.grifo ?? '-'}</td>
                            <td>${c.fecha_hora ?? '-'}</td>
                            <td>${c.galonesCombustible ?? '-'}</td>
                            <td>${c.importe ?? '-'}</td>
                            <td>${c.kilometraje_inicial ?? '-'}</td>
                            <td>${c.kilometraje_final ?? '-'}</td>
                            <td>${c.tipo_combustible ?? '-'}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editCombustible(${c.id})">Editar</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteCombustible(${c.id})">Eliminar</button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                rows = '<tr><td colspan="10">No hay registros de combustible para esta ruta.</td></tr>';
            }
            $('#combustible-list').html(rows);
        }).fail(() => {
            $('#combustible-list').html('<tr><td colspan="10" class="text-danger">Error al cargar combustibles.</td></tr>');
        });
    }

    // Guardar combustible (nuevo o edición)
    $('#combustibleForm').on('submit', function(e){
        e.preventDefault();

        let id = $('#combustibleId').val();
        let data = {
            ruta_id: rutaId, // 👈 importante
            num_factura: $('#num_factura').val(),
            grifo: $('#grifo').val(),
            fecha_hora: $('#fecha_hora').val(),
            galonesCombustible: $('#galonesCombustible').val(),
            importe: $('#importe').val(),
            kilometraje_inicial: $('#kilometraje_inicial').val(),
            kilometraje_final: $('#kilometraje_final').val(),
            tipo_combustible: $('#tipo_combustible').val(),
        };

        if(id){ // editar
            $.ajax({
                url: `${apiUrl}/${id}`,
                method: 'PUT',
                data: data,
                success: function(){
                    $('#combustibleModal').modal('hide');
                    fetchCombustible();
                }
            });
        } else { // nuevo
            $.post(apiUrl, data, function(){
                $('#combustibleModal').modal('hide');
                fetchCombustible();
            });
        }
    });

    // Editar combustible
    function editCombustible(id){
        $.get(`${apiUrl}/${id}`, function(c){
            $('#combustibleId').val(c.id);
            $('#num_factura').val(c.num_factura);
            $('#grifo').val(c.grifo);
            $('#fecha_hora').val(c.fecha_hora);
            $('#galonesCombustible').val(c.galonesCombustible);
            $('#importe').val(c.importe);
            $('#kilometraje_inicial').val(c.kilometraje_inicial);
            $('#kilometraje_final').val(c.kilometraje_final);
            $('#tipo_combustible').val(c.tipo_combustible);
            $('#ruta_id').val(rutaId); // 👈 siempre la ruta actual

            $('#combustibleModal').modal('show');
        });
    }

    // Eliminar combustible
    function deleteCombustible(id){
        if(confirm("¿Seguro que deseas eliminar este registro?")){
            $.ajax({
                url: `${apiUrl}/${id}`,
                method: 'DELETE',
                success: function(){
                    fetchCombustible();
                }
            });
        }
    }

    // Abrir modal nuevo
    $('#btn-nuevo').click(() => {
        $('#combustibleForm')[0].reset();
        $('#combustibleId').val('');
        $('#ruta_id').val(rutaId);
        $('#combustibleModal').modal('show');
    });

    $(document).ready(function(){
        fetchRuta();
        fetchCombustible();
    });
</script>
@endpush
