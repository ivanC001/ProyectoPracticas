@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="module-shell">
            <div class="card module-card">
                <div class="module-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="module-heading">
                            <div class="module-icon">
                                <i class="fas fa-gas-pump"></i>
                            </div>
                            <div>
                                <h3 class="module-title">Combustible</h3>
                                <p class="module-subtitle">Centraliza consumos, kilometraje y comprobantes con el mismo estilo de gestion del sistema.</p>
                            </div>
                        </div>

                        <div class="module-header-actions">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalRegistroCombustible">
                                <i class="fas fa-plus-circle"></i> Nuevo registro
                            </button>
                        </div>
                    </div>
                </div>

                <div class="module-body">
                    <div class="module-search mb-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text"
                                   class="form-control"
                                   id="buscador"
                                   placeholder="Buscar por factura, grifo, ruta o tipo de combustible...">
                        </div>
                    </div>

                    <div class="module-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover module-table text-center">
                                <thead>
                                    <tr>
                                        <th>Acciones</th>
                                        <th>ID</th>
                                        <th>Ruta</th>
                                        <th>Factura</th>
                                        <th>Grifo</th>
                                        <th>Fecha y hora</th>
                                        <th>Galones</th>
                                        <th>Importe</th>
                                        <th>Km inicial</th>
                                        <th>Km final</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody id="combustibleTableBody">
                                    <tr>
                                        <td colspan="11" class="module-empty">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('combustible.registro')
@endsection

@push('scripts')
<script>
let combustiblesSource = [];

function getCombustibleRutaInfo(combustible) {
    if (!combustible.ruta) {
        return 'Sin ruta';
    }

    return `#${combustible.ruta.id} - ${combustible.ruta.origen || '-'} a ${combustible.ruta.destino || '-'}`;
}

function renderCombustibles(rows) {
    const tbody = $('#combustibleTableBody');

    if (!rows.length) {
        tbody.html(`
            <tr>
                <td colspan="11" class="module-empty">No se encontraron registros de combustible.</td>
            </tr>
        `);
        return;
    }

    let html = '';

    rows.forEach(combustible => {
        html += `
            <tr>
                <td>
                    <div class="table-action-group">
                        <button type="button" class="btn btn-soft-warning btn-sm" onclick="editar(${combustible.id})" title="Editar registro">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-soft-danger btn-sm" onclick="eliminar(${combustible.id})" title="Eliminar registro">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
                <td>${combustible.id}</td>
                <td class="text-left">${getCombustibleRutaInfo(combustible)}</td>
                <td>${combustible.num_factura || '-'}</td>
                <td class="text-left">${combustible.grifo || '-'}</td>
                <td>${combustible.fecha_hora || '-'}</td>
                <td>${combustible.galonesCombustible || '-'}</td>
                <td>S/ ${Number(combustible.importe || 0).toFixed(2)}</td>
                <td>${combustible.kilometraje_inicial || '-'}</td>
                <td>${combustible.kilometraje_final || '-'}</td>
                <td>${combustible.tipo_combustible || '-'}</td>
            </tr>
        `;
    });

    tbody.html(html);
}

function applyCombustiblesFilter() {
    const value = $('#buscador').val().toLowerCase().trim();

    if (!value) {
        renderCombustibles(combustiblesSource);
        return;
    }

    const filtered = combustiblesSource.filter(combustible => {
        const content = [
            combustible.num_factura,
            combustible.grifo,
            combustible.fecha_hora,
            combustible.tipo_combustible,
            combustible.ruta?.origen,
            combustible.ruta?.destino,
            combustible.ruta?.id,
        ].join(' ').toLowerCase();

        return content.includes(value);
    });

    renderCombustibles(filtered);
}

function fetchCombustibles() {
    apiFetch('/api/combustibles')
        .then(response => {
            combustiblesSource = Array.isArray(response) ? response : [];
            renderCombustibles(combustiblesSource);
        })
        .catch(() => {
            $('#combustibleTableBody').html(`
                <tr>
                    <td colspan="11" class="module-empty text-danger">No se pudieron cargar los registros de combustible.</td>
                </tr>
            `);
        });
}

function eliminar(id) {
    Swal.fire({
        title: 'Eliminar registro?',
        text: 'Esta accion no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            method: 'DELETE',
            url: `/api/combustibles/${id}`,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Registro eliminado',
                    timer: 1400,
                    showConfirmButton: false
                });
                fetchCombustibles();
            },
            error: function () {
                Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
            }
        });
    });
}

$('#buscador').on('input', applyCombustiblesFilter);

$(document).ready(function () {
    fetchCombustibles();
});
</script>
@endpush
