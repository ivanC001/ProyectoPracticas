@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="mb-0 font-weight-bold">
                        <i class="fas fa-truck-moving text-primary"></i> Gestion de Tractos y Trailers
                    </h3>
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalRegistroCamion">
                        <i class="fas fa-plus-circle"></i> Nueva Unidad
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <input type="text"
                        id="searchTextCamion"
                        class="form-control"
                        placeholder="Buscar por placa, MTC o color...">
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped text-center">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Acciones</th>
                                <th>ID</th>
                                <th>Tracto</th>
                                <th>Trailer</th>
                                <th>Color</th>
                                <th>MTC</th>
                                <th>Seguros</th>
                                <th>Proximo vencimiento</th>
                                <th>Fecha ingreso</th>
                                <th>Conductores asignados</th>
                            </tr>
                        </thead>
                        <tbody id="camionTableBody"></tbody>
                    </table>
                </div>

                <div id="paginacionCamion" class="mt-3 text-center"></div>
            </div>
        </div>
    </div>
</div>

@include('camion.registro')
@include('camion.seguros')
@endsection

@push('scripts')
<script>
let searchCamion = '';
let debounceCamion;
let paginaCamionActual = 1;

function renderSeguroBadge(camion) {
    if ((camion.seguros_vencidos_count || 0) > 0) {
        return `<span class="badge badge-danger">${camion.seguros_vencidos_count} vencido(s)</span>`;
    }

    if ((camion.seguros_por_vencer_count || 0) > 0) {
        return `<span class="badge badge-warning">${camion.seguros_por_vencer_count} por vencer</span>`;
    }

    if (camion.proximo_seguro) {
        return '<span class="badge badge-success">Al dia</span>';
    }

    return '<span class="badge badge-secondary">Sin seguros</span>';
}

function renderProximoSeguro(camion) {
    if (!camion.proximo_seguro) {
        return '<span class="text-muted">Sin registro</span>';
    }

    const dias = Number(camion.proximo_seguro.dias_restantes || 0);
    const clase = dias < 0 ? 'text-danger' : (dias <= 30 ? 'text-warning' : 'text-success');

    return `
        <div class="text-left">
            <strong>${camion.proximo_seguro.tipo_seguro}</strong>
            <small class="d-block ${clase}">
                ${camion.proximo_seguro.fecha_vencimiento} · ${dias < 0 ? `Vencido hace ${Math.abs(dias)} dia(s)` : `Vence en ${dias} dia(s)`}
            </small>
        </div>
    `;
}

$('#searchTextCamion').on('input', function () {
    const texto = $(this).val().trim();

    clearTimeout(debounceCamion);
    debounceCamion = setTimeout(() => {
        searchCamion = texto.length >= 2 ? texto : '';
        fetchCamiones(paginaCamionActual = 1);
    }, 300);
});

function renderCamionPagination(pagination = {}) {
    let html = '';

    for (let i = 1; i <= (pagination.last_page || 1); i++) {
        html += `
            <button class="btn btn-sm ${i === pagination.current_page ? 'btn-primary' : 'btn-light'}"
                onclick="fetchCamiones(${i})">
                ${i}
            </button>
        `;
    }

    $('#paginacionCamion').html(html);
}

function fetchCamiones(page = 1) {
    paginaCamionActual = page;

    apiFetch(`/api/camiones?search=${encodeURIComponent(searchCamion)}&page=${page}`)
        .then(resp => {
            const tbody = $('#camionTableBody');
            tbody.empty();

            if (!resp.data.length) {
                tbody.html(`
                    <tr>
                        <td colspan="10" class="text-center text-muted">
                            No se encontraron unidades
                        </td>
                    </tr>
                `);
                $('#paginacionCamion').html('');
                return;
            }

            resp.data.forEach(camion => {
                tbody.append(`
                    <tr>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editarUnidad(${camion.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-info btn-sm" onclick="abrirSeguros(${camion.id}, '${camion.placa_tracto || ''}', '${camion.placa_carreto || ''}')">
                                <i class="fas fa-shield-alt"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarCamion(${camion.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                        <td>${camion.id}</td>
                        <td><strong>${camion.placa_tracto || '-'}</strong></td>
                        <td><strong>${camion.placa_carreto || '-'}</strong></td>
                        <td>${camion.color || '-'}</td>
                        <td>${camion.mtc || '-'}</td>
                        <td>${renderSeguroBadge(camion)}</td>
                        <td>${renderProximoSeguro(camion)}</td>
                        <td>${camion.fecha_ingreso || '-'}</td>
                        <td>
                            <span class="badge badge-${(camion.conductores_count || 0) > 0 ? 'success' : 'secondary'}">
                                ${camion.conductores_count || 0}
                            </span>
                        </td>
                    </tr>
                `);
            });

            renderCamionPagination(resp.pagination);
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo cargar la flota', 'error');
        });
}

function eliminarCamion(id) {
    Swal.fire({
        title: '¿Eliminar unidad?',
        icon: 'warning',
        showCancelButton: true
    }).then(result => {
        if (!result.isConfirmed) {
            return;
        }

        apiFetch(`/api/camiones/${id}`, {
            method: 'DELETE'
        }).then(resp => {
            Swal.fire('OK', resp.message, 'success');
            fetchCamiones(paginaCamionActual);
        });
    });
}

$(document).ready(() => {
    fetchCamiones();
});
</script>
@endpush
