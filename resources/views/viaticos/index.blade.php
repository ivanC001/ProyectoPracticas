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
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div>
                                <h3 class="module-title">Viaticos</h3>
                                <p class="module-subtitle">Consulta y registra gastos operativos con el mismo formato del resto del panel.</p>
                            </div>
                        </div>

                        <div class="module-header-actions">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalRegistroViatico">
                                <i class="fas fa-plus-circle"></i> Nuevo viatico
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
                                   placeholder="Buscar por servicio, ruta, factura o descripcion...">
                        </div>
                    </div>

                    <div class="module-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover module-table text-center">
                                <thead>
                                    <tr>
                                        <th>Acciones</th>
                                        <th>ID</th>
                                        <th>Servicio</th>
                                        <th>Ruta</th>
                                        <th>Fecha</th>
                                        <th>Factura</th>
                                        <th>Importe</th>
                                        <th>Descripcion</th>
                                    </tr>
                                </thead>
                                <tbody id="viaticoTableBody">
                                    <tr>
                                        <td colspan="8" class="module-empty">
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

@include('viaticos.registro')
@endsection

@push('scripts')
<script>
let viaticosSource = [];

function getRutaInfo(viatico) {
    if (!viatico.ruta) {
        return 'Sin ruta';
    }

    return `#${viatico.ruta.id} - ${viatico.ruta.origen || '-'} a ${viatico.ruta.destino || '-'}`;
}

function renderViaticos(rows) {
    const tbody = $('#viaticoTableBody');

    if (!rows.length) {
        tbody.html(`
            <tr>
                <td colspan="8" class="module-empty">No se encontraron viaticos.</td>
            </tr>
        `);
        return;
    }

    let html = '';

    rows.forEach(viatico => {
        html += `
            <tr>
                <td>
                    <div class="table-action-group">
                        <button type="button" class="btn btn-soft-warning btn-sm" onclick="editarViatico(${viatico.id})" title="Editar viatico">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-soft-danger btn-sm" onclick="eliminar(${viatico.id})" title="Eliminar viatico">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
                <td>${viatico.id}</td>
                <td class="text-left">${viatico.nombre_servicio || '-'}</td>
                <td class="text-left">${getRutaInfo(viatico)}</td>
                <td>${viatico.fecha || '-'}</td>
                <td>${viatico.numero_factura || '-'}</td>
                <td>S/ ${Number(viatico.importe || 0).toFixed(2)}</td>
                <td class="text-left">${viatico.descripcion || '-'}</td>
            </tr>
        `;
    });

    tbody.html(html);
}

function applyViaticosFilter() {
    const value = $('#buscador').val().toLowerCase().trim();

    if (!value) {
        renderViaticos(viaticosSource);
        return;
    }

    const filtered = viaticosSource.filter(viatico => {
        const content = [
            viatico.nombre_servicio,
            viatico.fecha,
            viatico.numero_factura,
            viatico.descripcion,
            viatico.ruta?.origen,
            viatico.ruta?.destino,
        ].join(' ').toLowerCase();

        return content.includes(value);
    });

    renderViaticos(filtered);
}

function fetchViaticos() {
    apiFetch('/api/viaticos')
        .then(response => {
            viaticosSource = Array.isArray(response) ? response : [];
            renderViaticos(viaticosSource);
        })
        .catch(() => {
            $('#viaticoTableBody').html(`
                <tr>
                    <td colspan="8" class="module-empty text-danger">No se pudieron cargar los viaticos.</td>
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
            url: `/api/viaticos/${id}`,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Viatico eliminado',
                    timer: 1400,
                    showConfirmButton: false
                });
                fetchViaticos();
            },
            error: function () {
                Swal.fire('Error', 'No se pudo eliminar el viatico.', 'error');
            }
        });
    });
}

$('#buscador').on('input', applyViaticosFilter);

$(document).ready(function () {
    fetchViaticos();
});
</script>
@endpush
