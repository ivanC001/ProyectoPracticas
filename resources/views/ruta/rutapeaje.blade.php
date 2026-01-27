@extends('admin.main')

@section('contenido')
<div class="content">
  <div class="container-fluid">
    <h4 class="text-center mb-4 fw-bold text-primary">
      <i class="bi bi-cash-stack me-2"></i>Detalle de Ruta y Peajes
    </h4>

    <!-- Información de la ruta -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-primary text-white">
        <strong><i class="bi bi-truck-front me-2"></i>Información de la Ruta</strong>
      </div>
      <div class="card-body" id="ruta-info">
        <div class="text-center text-muted">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2">Cargando datos...</p>
        </div>
      </div>
    </div>

<!-- Lista de registros de peajes -->
<div class="card shadow-sm border-0">
  <div class="card-header bg-warning d-flex justify-content-between align-items-center fw-semibold">
    <span><i class="bi bi-cash-coin me-2"></i>Registro de Peajes</span>
    <button class="btn btn-light btn-sm" id="btn-nuevo-peaje">
      <i class="bi bi-plus-circle me-1"></i>Nuevo
    </button>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle text-center mb-0">
        <thead class="table-warning">
          <tr>
            <th style="width: 5%">#</th>
            <th style="width: 20%">Nombre</th>
            <th style="width: 15%">Importe (S/)</th>
            <th style="width: 20%">Fecha y Hora</th>
            <th style="width: 20%">Comprobante</th>
            <th style="width: 20%">Acciones</th>
          </tr>
        </thead>
        <tbody id="peaje-list">
          <tr>
            <td colspan="6" class="text-muted py-3">Cargando registros de peajes...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>


<!-- Modal para registrar/editar peaje -->
<div class="modal fade" id="peajeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="peajeForm">
        <div class="modal-header bg-warning">
          <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Registrar Peaje</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="peajeId">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" id="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Importe (S/)</label>
              <input type="number" step="0.01" id="importe" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha y Hora</label>
              <input type="datetime-local" id="fecha_hora" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Comprobante</label>
              <input type="text" id="comprobante" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-save2 me-1"></i>Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Bundle con Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



@endsection

@push('scripts')
<!-- Bootstrap Icons y SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const rutaId = window.location.pathname.split('/')[2];
let apiPeajeUrl = `/api/rutas/${rutaId}/peajes`;

// Cargar información de la ruta (compacta y elegante)
function fetchRuta() {
  $.get(`/api/rutas/${rutaId}`, function(response) {
    $('#ruta-info').html(`
      <div class="row g-3 ">
        <div class="col-md-6 col-lg-4"><i class="bi bi-geo-alt-fill text-danger me-2"></i><strong>Origen:</strong> ${response.origen}</div>
        <div class="col-md-6 col-lg-4"><i class="bi bi-flag-fill text-success me-2"></i><strong>Destino:</strong> ${response.destino}</div>
        <div class="col-md-6 col-lg-4"><i class="bi bi-person-fill text-primary me-2"></i><strong>Conductor:</strong> ${response.conductor?.nombre ?? 'N/A'}</div>
        <div class="col-md-6 col-lg-4"><i class="bi bi-truck text-warning me-2"></i><strong>Vehículo:</strong> ${response.camion?.placa_tracto ?? 'N/A'} / ${response.camion?.placa_carreto ?? 'N/A'}</div>
        <div class="col-md-12"><i class="bi bi-calendar-date text-info me-2"></i><strong>Fechas:</strong> ${response.fecha_inicio} — ${response.fecha_fin}</div>
      </div>
    `);
  }).fail(() => {
    $('#ruta-info').html(`
      <div class="alert alert-danger text-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>Error al cargar los datos de la ruta.
      </div>
    `);
  });
}

// Cargar peajes
function fetchPeajes() {
  $.get(apiPeajeUrl, function(response) {
    let rows = '';
    if (response.length > 0) {
      response.forEach((p, i) => {
        rows += `
          <tr>
            <td>${i + 1}</td>
            <td>${p.nombre ?? '-'}</td>
            <td>${p.importe ?? '-'}</td>
            <td>${p.fecha_hora ?? '-'}</td>
            <td>${p.comprobante ?? '-'}</td>
            <td>
              <button class="btn btn-sm btn-outline-warning me-1" onclick="editPeaje(${p.id})"><i class="bi bi-pencil-square"></i></button>
              <button class="btn btn-sm btn-outline-danger" onclick="deletePeaje(${p.id})"><i class="bi bi-trash3"></i></button>
            </td>
          </tr>`;
      });
    } else {
      rows = '<tr><td colspan="6">No hay registros de peajes para esta ruta.</td></tr>';
    }
    $('#peaje-list').html(rows);
  }).fail(() => {
    $('#peaje-list').html('<tr><td colspan="6" class="text-danger">Error al cargar peajes.</td></tr>');
  });
}

// Guardar peaje
$('#peajeForm').on('submit', function(e){
  e.preventDefault();
  let id = $('#peajeId').val();
  let data = {
    nombre: $('#nombre').val(),
    importe: $('#importe').val(),
    fecha_hora: $('#fecha_hora').val(),
    comprobante: $('#comprobante').val()
  };

  const method = id ? 'PUT' : 'POST';
  const url = id ? `${apiPeajeUrl}/${id}` : apiPeajeUrl;

  $.ajax({ url, method, data })
    .done(() => {
      $('#peajeModal').modal('hide');
      fetchPeajes();
      Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: id ? 'Peaje actualizado correctamente.' : 'Peaje registrado correctamente.',
        timer: 1800,
        showConfirmButton: false
      });
    })
    .fail(() => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo guardar el peaje. Inténtalo de nuevo.',
      });
    });
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

// Eliminar peaje con Swal
function deletePeaje(id){
  Swal.fire({
    title: '¿Eliminar peaje?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: `${apiPeajeUrl}/${id}`,
        method: 'DELETE',
        success: function(){
          fetchPeajes();
          Swal.fire({
            icon: 'success',
            title: 'Eliminado',
            text: 'El peaje fue eliminado correctamente.',
            timer: 1500,
            showConfirmButton: false
          });
        },
        error: function(){
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo eliminar el registro.'
          });
        }
      });
    }
  });
}

// Nuevo peaje
$('#btn-nuevo-peaje').click(() => {
  $('#peajeForm')[0].reset();
  $('#peajeId').val('');
  $('#peajeModal').modal('show');
});

// Inicializar
$(document).ready(() => {
  fetchRuta();
  fetchPeajes();
});
</script>
@endpush
    