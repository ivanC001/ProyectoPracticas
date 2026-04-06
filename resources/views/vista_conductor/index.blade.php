@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="mb-0 font-weight-bold">
                        <i class="fas fa-id-card-alt text-primary"></i> Gestion de Conductores
                    </h3>
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalRegistroConductor">
                        <i class="fas fa-plus-circle"></i> Nuevo Conductor
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <input type="text"
                        id="searchText"
                        class="form-control"
                        placeholder="Buscar por nombre, licencia o placa asignada...">
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped text-center">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Acciones</th>
                                <th>ID</th>
                                <th>Conductor</th>
                                <th>Licencia</th>
                                <th>Telefono</th>
                                <th>Email</th>
                                <th>Ciudad</th>
                                <th>Tracto</th>
                                <th>Trailer</th>
                            </tr>
                        </thead>
                        <tbody id="conductorTableBody"></tbody>
                    </table>
                </div>

                <div id="paginacion" class="mt-3 text-center"></div>
            </div>
        </div>
    </div>
</div>

@include('vista_conductor.registro')
@endsection

@push('scripts')
<script>
let searchConductor = '';
let debounceConductor;
let paginaConductorActual = 1;

$('#searchText').on('input', function () {
    const texto = $(this).val().trim();

    clearTimeout(debounceConductor);
    debounceConductor = setTimeout(() => {
        searchConductor = texto.length >= 2 ? texto : '';
        fetchConductores(1);
    }, 300);
});

function renderConductorPagination(pagination = {}) {
    let html = '';

    for (let i = 1; i <= (pagination.last_page || 1); i++) {
        html += `
            <button class="btn btn-sm ${i === pagination.current_page ? 'btn-primary' : 'btn-light'}"
                onclick="fetchConductores(${i})">
                ${i}
            </button>
        `;
    }

    $('#paginacion').html(html);
}

function fetchConductores(page = 1) {
    paginaConductorActual = page;

    apiFetch(`/api/conductores?search=${encodeURIComponent(searchConductor)}&page=${page}`)
        .then(resp => {
            const tbody = $('#conductorTableBody');
            tbody.empty();

            if (!resp.data.length) {
                tbody.html(`
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            No se encontraron conductores
                        </td>
                    </tr>
                `);
                $('#paginacion').html('');
                return;
            }

            resp.data.forEach(conductor => {
                const nombreCompleto = `${conductor.nombre || ''} ${conductor.apellido || ''}`.trim();

                tbody.append(`
                    <tr>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editar(${conductor.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarConductor(${conductor.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                        <td>${conductor.id}</td>
                        <td class="text-left">
                            <strong>${nombreCompleto || '-'}</strong>
                            <small class="d-block text-muted">${conductor.genero || '-'}</small>
                        </td>
                        <td>
                            <span class="badge badge-info">${conductor.tipo_licencia || '-'}</span>
                            <div>${conductor.licencia || '-'}</div>
                        </td>
                        <td>${conductor.telefono || '-'}</td>
                        <td class="text-left">${conductor.email || '-'}</td>
                        <td>${conductor.ciudad || '-'}</td>
                        <td>${conductor.camion?.placa_tracto || '-'}</td>
                        <td>${conductor.camion?.placa_carreto || '-'}</td>
                    </tr>
                `);
            });

            renderConductorPagination(resp.pagination);
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo cargar conductores', 'error');
        });
}

function eliminarConductor(id) {
    Swal.fire({
        title: '¿Eliminar conductor?',
        icon: 'warning',
        showCancelButton: true
    }).then(result => {
        if (!result.isConfirmed) {
            return;
        }

        apiFetch(`/api/conductores/${id}`, {
            method: 'DELETE'
        }).then(resp => {
            Swal.fire('OK', resp.message, 'success');
            fetchConductores(paginaConductorActual);
        });
    });
}

$(document).ready(() => {
    fetchConductores();
});
</script>
@endpush
