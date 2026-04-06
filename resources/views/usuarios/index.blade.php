@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-users-cog text-primary"></i> Usuarios y roles
                    </h4>
                    <small class="text-muted">Controla acceso, rol y estado de cada usuario del sistema.</small>
                </div>

                <button class="btn btn-primary mt-2 mt-md-0"
                        data-toggle="modal"
                        data-target="#modalRegistroUsuario">
                    <i class="fas fa-plus"></i> Nuevo usuario
                </button>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="input-group shadow-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                            </div>
                            <input type="text"
                                   id="searchText"
                                   class="form-control"
                                   placeholder="Buscar por nombre, correo o rol...">
                        </div>
                    </div>

                    <div class="col-md-3 mb-2 mb-md-0">
                        <select id="filterRol" class="form-control">
                            <option value="">Todos los roles</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select id="filterEstado" class="form-control">
                            <option value="">Todos los estados</option>
                            <option value="true">Activos</option>
                            <option value="false">Inactivos</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped text-center">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Acciones</th>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="usuarioTableBody">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border text-primary"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="paginacion" class="mt-3 text-center"></div>
            </div>
        </div>
    </div>
</div>

@include('usuarios.registro')
@endsection

@push('scripts')
<script>
const ROLE_OPTIONS = @json(config('roles.definitions', []));

let searchGlobal = '';
let currentPage = 1;
let debounceTimer;
let currentAuthUser = null;

function populateRoleFilter() {
    const filter = $('#filterRol');

    Object.entries(ROLE_OPTIONS).forEach(([value, role]) => {
        filter.append(`<option value="${value}">${role.label}</option>`);
    });
}

function renderPagination(pagination) {
    let html = '';

    for (let i = 1; i <= pagination.last_page; i++) {
        html += `
            <button class="btn btn-sm ${i === pagination.current_page ? 'btn-primary' : 'btn-light'}"
                onclick="fetchUsuarios(${i})">
                ${i}
            </button>
        `;
    }

    $('#paginacion').html(html);
}

function getRoleBadge(user) {
    const palette = {
        admin: 'badge-danger',
        comercial: 'badge-info',
        operaciones: 'badge-warning'
    };

    const css = palette[user.rol] || 'badge-secondary';
    return `<span class="badge ${css}">${user.rol_label || user.rol}</span>`;
}

function getStatusBadge(active) {
    return active
        ? '<span class="badge badge-success">Activo</span>'
        : '<span class="badge badge-secondary">Inactivo</span>';
}

function fetchUsuarios(page = 1) {
    currentPage = page;

    const params = new URLSearchParams({
        page,
        search: searchGlobal,
        rol: $('#filterRol').val() || '',
        activo: $('#filterEstado').val() || ''
    });

    apiFetch(`/api/usuarios?${params.toString()}`)
        .then((response) => {
            const tbody = $('#usuarioTableBody');
            tbody.empty();

            if (!currentAuthUser) {
                currentAuthUser = JSON.parse(localStorage.getItem('auth_user') || 'null');
            }

            if (!response.data.length) {
                tbody.html(`
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No se encontraron usuarios
                        </td>
                    </tr>
                `);
                $('#paginacion').html('');
                return;
            }

            response.data.forEach((usuario) => {
                const disableDelete = currentAuthUser && Number(currentAuthUser.id) === Number(usuario.id);

                tbody.append(`
                    <tr>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editarUsuario(${usuario.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="desactivarUsuario(${usuario.id})" ${disableDelete ? 'disabled' : ''}>
                                <i class="fas fa-user-slash"></i>
                            </button>
                        </td>
                        <td>${usuario.id}</td>
                        <td class="text-left">${usuario.name}</td>
                        <td class="text-left">${usuario.email}</td>
                        <td>${getRoleBadge(usuario)}</td>
                        <td>${getStatusBadge(usuario.activo)}</td>
                    </tr>
                `);
            });

            renderPagination(response.pagination);
        })
        .catch((error) => {
            Swal.fire('Error', error.message || 'No se pudo cargar usuarios', 'error');
        });
}

function editarUsuario(id) {
    apiFetch(`/api/usuarios/${id}`)
        .then((response) => {
            const usuario = response.data;

            $('#usuarioId').val(usuario.id);
            $('#name').val(usuario.name);
            $('#email').val(usuario.email);
            $('#rol').val(usuario.rol);
            $('#activo').val(usuario.activo ? '1' : '0');
            $('#password').val('');
            $('#password_confirmation').val('');

            window.editingUsuarioId = id;

            $('#grupoActivo').show();
            $('#passwordHelp').text('Deja la contrasena vacia si no deseas cambiarla.');
            $('#modalRegistroUsuarioLabel').text('Editar usuario');
            $('#modalRegistroUsuario').modal('show');
        });
}

function desactivarUsuario(id) {
    Swal.fire({
        title: '¿Desactivar usuario?',
        text: 'El usuario perdera acceso hasta volver a activarlo.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, desactivar'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        apiFetch(`/api/usuarios/${id}`, { method: 'DELETE' })
            .then((response) => {
                Swal.fire('Listo', response.message, 'success');
                fetchUsuarios(currentPage);
            })
            .catch((error) => {
                Swal.fire('Error', error.message || 'No se pudo desactivar el usuario', 'error');
            });
    });
}

$('#searchText').on('keyup', function () {
    clearTimeout(debounceTimer);
    const text = $(this).val().trim();

    debounceTimer = setTimeout(() => {
        searchGlobal = text.length >= 2 ? text : '';
        fetchUsuarios(1);
    }, 350);
});

$('#filterRol, #filterEstado').on('change', function () {
    fetchUsuarios(1);
});

$(document).ready(function () {
    populateRoleFilter();
    fetchUsuarios();
});
</script>
@endpush
