<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <title>Sistema de Gestion</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/dist/img/AdminLTELogo.png') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">

    <style>
        body {
            background: linear-gradient(180deg, #f4f8fc 0%, #eef3f9 100%);
            color: #1f2937;
        }

        .main-header.navbar {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .main-sidebar {
            background:
                radial-gradient(circle at top, rgba(56, 189, 248, 0.22), transparent 34%),
                linear-gradient(180deg, #0f172a 0%, #102a43 42%, #0b1f33 100%) !important;
            border-right: 1px solid rgba(148, 163, 184, 0.08);
            z-index: 1040;
        }

        .brand-link {
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
            padding: 18px 16px;
            background: rgba(255, 255, 255, 0.03);
        }

        .brand-shell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo-full {
            width: 54px;
            height: 54px;
            object-fit: contain;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.96);
            padding: 6px;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.18);
        }

        .brand-copy {
            color: #f8fafc;
            min-width: 0;
        }

        .brand-title {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.1;
            margin: 0;
            letter-spacing: 0.2px;
        }

        .brand-subtitle {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(226, 232, 240, 0.75);
            margin-top: 4px;
        }

        .sidebar {
            padding: 14px 12px 28px;
            overflow-x: hidden;
        }

        .sidebar-user-card {
            border: 1px solid rgba(148, 163, 184, 0.12);
            background: rgba(15, 23, 42, 0.32);
            border-radius: 18px;
            padding: 14px;
            margin-bottom: 14px;
            color: #e2e8f0;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
            min-width: 0;
            overflow: hidden;
        }

        .sidebar-user-card small {
            display: block;
            color: rgba(226, 232, 240, 0.68);
            text-transform: uppercase;
            letter-spacing: .9px;
            margin-bottom: 6px;
            font-size: 10px;
        }

        .sidebar-user-card strong {
            display: block;
            font-size: 14px;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.25;
            max-width: 100%;
        }

        .sidebar-user-card span {
            display: block;
            margin-top: 6px;
            font-size: 14px;
            font-weight: 600;
            color: rgba(226, 232, 240, 0.92);
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.25;
            max-width: 100%;
        }

        .sidebar-user-role {
            display: inline-flex;
            margin-top: 10px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(14, 165, 233, 0.14);
            color: #bae6fd;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            max-width: 100%;
            white-space: normal;
            overflow-wrap: anywhere;
            text-align: center;
        }

        .nav-section-label {
            color: rgba(191, 219, 254, 0.72);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.1px;
            padding: 12px 12px 8px;
        }

        .nav-sidebar .nav-item {
            margin-bottom: 6px;
        }

        .nav-sidebar .nav-link {
            border-radius: 14px;
            padding: 11px 14px;
            color: rgba(226, 232, 240, 0.86);
            transition: all .2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .nav-sidebar .nav-link .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 14px;
        }

        .nav-sidebar .nav-link:hover {
            background: rgba(59, 130, 246, 0.16);
            color: #fff;
            transform: translateX(2px);
        }

        .nav-sidebar .nav-link.active {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            color: #fff;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.28);
        }

        .nav-sidebar .nav-link .nav-pill {
            margin-left: auto;
            font-size: 10px;
            background: rgba(255, 255, 255, 0.14);
            padding: 2px 8px;
            border-radius: 999px;
        }

        .nav-sidebar .nav-link p {
            margin: 0;
        }

        .nav-sidebar .nav-treeview {
            padding-left: 14px;
        }

        .nav-sidebar .nav-treeview > .nav-item {
            margin-bottom: 4px;
        }

        .nav-sidebar .nav-treeview > .nav-item > .nav-link {
            border-radius: 12px;
            padding: 9px 12px;
            font-size: 13px;
            background: rgba(255, 255, 255, 0.03);
        }

        .nav-sidebar .nav-treeview > .nav-item > .nav-link .nav-icon {
            font-size: 12px;
            width: 16px;
        }

        .content-wrapper {
            background: transparent;
        }

        .content-header {
            padding: 18px 22px 0;
        }

        .main-footer {
            background: rgba(255, 255, 255, 0.9);
            border-top: 1px solid rgba(148, 163, 184, 0.15);
            color: #475569;
            position: relative;
            z-index: 1020;
        }

        .module-shell {
            padding-bottom: 24px;
        }

        .module-card {
            border: 0;
            border-radius: 24px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }

        .module-header {
            padding: 22px 24px 16px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.95);
            background:
                radial-gradient(circle at right top, rgba(14, 165, 233, 0.14), transparent 28%),
                linear-gradient(180deg, rgba(248, 250, 252, 0.98) 0%, rgba(255, 255, 255, 0.94) 100%);
        }

        .module-heading {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .module-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
            color: #0f4c81;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
            font-size: 20px;
            flex: 0 0 auto;
        }

        .module-title {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -.02em;
        }

        .module-subtitle {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .module-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .module-body {
            padding: 22px 24px 24px;
        }

        .module-search .input-group-text,
        .module-search .form-control {
            min-height: 48px;
            border-color: #dbe5f0;
        }

        .module-search .input-group-text {
            background: #fff;
            color: #64748b;
            border-radius: 14px 0 0 14px;
        }

        .module-search .form-control {
            border-radius: 0 14px 14px 0;
            box-shadow: none;
        }

        .module-search .form-control:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.12);
        }

        .module-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .module-table {
            margin-bottom: 0;
        }

        .module-table thead th {
            border: 0;
            background: linear-gradient(135deg, #0f5fcf 0%, #1891ff 100%);
            color: #fff;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .04em;
            vertical-align: middle;
            white-space: nowrap;
        }

        .module-table tbody td {
            vertical-align: middle;
            border-color: #edf2f7;
        }

        .module-table tbody tr:hover {
            background: #f8fbff;
        }

        .module-pagination {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .module-empty {
            padding: 28px 12px;
            text-align: center;
            color: #64748b;
        }

        .table-action-group {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .content-wrapper .btn,
        .content-wrapper .btn:focus,
        .modal .btn,
        .modal .btn:focus {
            border-radius: 14px;
            font-weight: 700;
            letter-spacing: .01em;
            min-height: 44px;
            padding: .68rem 1.15rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .42rem;
            border-width: 1px;
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
            transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
        }

        .content-wrapper .btn:hover,
        .modal .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12);
        }

        .content-wrapper .btn:disabled,
        .modal .btn:disabled {
            opacity: .55;
            box-shadow: none;
            transform: none;
        }

        .content-wrapper .btn-sm,
        .modal .btn-sm {
            min-height: 34px;
            padding: .42rem .72rem;
            border-radius: 10px;
            font-size: 12px;
            box-shadow: none;
        }

        .content-wrapper .btn-lg,
        .modal .btn-lg {
            min-height: 50px;
            border-radius: 16px;
            padding: .82rem 1.35rem;
        }

        .content-wrapper .btn-block,
        .modal .btn-block {
            width: 100%;
        }

        .content-wrapper .btn-primary,
        .modal .btn-primary {
            background: linear-gradient(135deg, #0f5fcf 0%, #1891ff 100%);
            border-color: #0f5fcf;
            color: #fff;
        }

        .content-wrapper .btn-success,
        .modal .btn-success {
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
            border-color: #16a34a;
            color: #fff;
        }

        .content-wrapper .btn-info,
        .modal .btn-info {
            background: linear-gradient(135deg, #0891b2 0%, #22d3ee 100%);
            border-color: #0891b2;
            color: #fff;
        }

        .content-wrapper .btn-warning,
        .modal .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            border-color: #f59e0b;
            color: #111827;
        }

        .content-wrapper .btn-danger,
        .modal .btn-danger {
            background: linear-gradient(135deg, #dc2626 0%, #f87171 100%);
            border-color: #dc2626;
            color: #fff;
        }

        .content-wrapper .btn-secondary,
        .modal .btn-secondary,
        .content-wrapper .btn-dark,
        .modal .btn-dark {
            background: linear-gradient(135deg, #334155 0%, #475569 100%);
            border-color: #334155;
            color: #fff;
        }

        .content-wrapper .btn-light,
        .modal .btn-light {
            background: #fff;
            border-color: #dbe5f0;
            color: #334155;
        }

        .content-wrapper .btn-outline-primary,
        .modal .btn-outline-primary {
            background: rgba(15, 95, 207, 0.08);
            border-color: rgba(15, 95, 207, 0.22);
            color: #0f5fcf;
        }

        .content-wrapper .btn-outline-secondary,
        .modal .btn-outline-secondary {
            background: rgba(148, 163, 184, 0.12);
            border-color: rgba(148, 163, 184, 0.24);
            color: #334155;
        }

        .content-wrapper .btn-outline-dark,
        .modal .btn-outline-dark {
            background: rgba(51, 65, 85, 0.08);
            border-color: rgba(51, 65, 85, 0.2);
            color: #0f172a;
        }

        .content-wrapper .btn-link,
        .modal .btn-link {
            box-shadow: none;
            min-height: auto;
            padding: 0;
            border-radius: 0;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-soft-primary {
            background: #e0f2fe;
            color: #075985;
            border-color: #bae6fd;
        }

        .btn-soft-secondary {
            background: #e2e8f0;
            color: #334155;
            border-color: #cbd5e1;
        }

        .btn-soft-warning {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        .btn-soft-danger {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .btn-soft-dark {
            background: #dbe4f0;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .topbar-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
        }

        .topbar-chip i {
            color: #0ea5e9;
        }

        .user-dropdown-trigger {
            border-radius: 999px;
            background: #fff;
            border: 1px solid rgba(148, 163, 184, 0.2);
            padding: 6px 12px;
        }

        .topbar-role {
            display: inline-flex;
            margin-left: 8px;
            padding: 3px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 700;
        }

        @media (max-width: 991.98px) {
            .brand-title {
                font-size: 16px;
            }

            .topbar-chip {
                display: none;
            }

            .module-header {
                padding: 20px 18px 14px;
            }

            .module-body {
                padding: 18px;
            }

            .module-header .d-flex {
                flex-direction: column;
                align-items: stretch !important;
            }

            .module-header-actions {
                margin-top: 14px;
            }

            .module-header-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
@php
    $menuSections = [
        [
            'key' => 'general',
            'label' => 'Vista General',
            'items' => [
                ['href' => '/', 'icon' => 'fas fa-home', 'text' => 'Inicio', 'match' => '', 'roles' => ['admin', 'comercial', 'operaciones']],
                ['href' => '/reporte-ruta', 'icon' => 'fas fa-chart-line', 'text' => 'Reportes de rutas', 'match' => 'reporte-ruta*', 'pill' => 'Nuevo', 'roles' => ['admin', 'operaciones']],
            ],
        ],
        [
            'key' => 'comercial',
            'label' => 'Comercial',
            'items' => [
                ['href' => '/clientes', 'icon' => 'fas fa-users', 'text' => 'Clientes', 'match' => 'clientes*', 'roles' => ['admin', 'comercial']],
                ['href' => '/cotizaciones', 'icon' => 'fas fa-file-signature', 'text' => 'Cotizaciones', 'match' => 'cotizaciones*', 'roles' => ['admin', 'comercial']],
                ['href' => '/venta', 'icon' => 'fas fa-cash-register', 'text' => 'Ventas', 'match' => 'venta*', 'roles' => ['admin', 'comercial']],
                ['href' => '/notascredito', 'icon' => 'fas fa-file-invoice-dollar', 'text' => 'Notas Cred/Deb', 'match' => 'notascredito*', 'roles' => ['admin', 'comercial']],
            ],
        ],
        [
            'key' => 'catalogo',
            'label' => 'Catalogo',
            'items' => [
                ['href' => '/producto', 'icon' => 'fas fa-boxes', 'text' => 'Productos', 'match' => 'producto*', 'roles' => ['admin', 'comercial']],
                ['href' => '/servicios', 'icon' => 'fas fa-concierge-bell', 'text' => 'Servicios', 'match' => 'servicios*', 'roles' => ['admin', 'comercial']],
            ],
        ],
        [
            'key' => 'transporte',
            'label' => 'Transporte',
            'items' => [
                ['href' => '/conductor', 'icon' => 'fas fa-id-card-alt', 'text' => 'Conductores', 'match' => 'conductor*', 'roles' => ['admin', 'operaciones']],
                ['href' => '/camion', 'icon' => 'fas fa-truck-moving', 'text' => 'Tractos y trailers', 'match' => 'camion*', 'roles' => ['admin', 'operaciones']],
                [
                    'href' => '/rutas',
                    'icon' => 'fas fa-route',
                    'text' => 'Rutas',
                    'roles' => ['admin', 'operaciones'],
                    'children' => [
                        ['href' => '/rutas', 'icon' => 'far fa-circle', 'text' => 'Resumen de rutas', 'match' => 'rutas*'],
                        ['href' => '/rutas', 'icon' => 'far fa-circle', 'text' => 'Viaticos por ruta', 'match' => 'ruta/*/rutaviatico*'],
                        ['href' => '/rutas', 'icon' => 'far fa-circle', 'text' => 'Combustible por ruta', 'match' => 'ruta/*/rutacombustible*'],
                        ['href' => '/rutas', 'icon' => 'far fa-circle', 'text' => 'Peajes por ruta', 'match' => 'ruta/*/rutapeaje*'],
                    ],
                ],
                ['href' => '/guias-remision', 'icon' => 'fas fa-truck-loading', 'text' => 'Guias de remision', 'match' => 'guias-remision*', 'roles' => ['admin', 'comercial', 'operaciones']],
            ],
        ],
        [
            'key' => 'sistema',
            'label' => 'Sistema',
            'items' => [
                ['href' => '/usuarios', 'icon' => 'fas fa-users-cog', 'text' => 'Usuarios y roles', 'match' => 'usuarios*', 'roles' => ['admin']],
            ],
        ],
    ];
    $roleDefinitions = config('roles.definitions', []);
@endphp
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-md-flex align-items-center">
                    <span class="topbar-chip">
                        <i class="fas fa-bolt"></i> Panel operativo y comercial
                    </span>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto align-items-center">
                <li class="nav-item dropdown">
                    <a class="nav-link user-dropdown-trigger" id="userDropdown" data-toggle="dropdown" href="#">
                        <i class="fas fa-user-circle text-primary mr-1"></i>
                        <span id="userName">Invitado</span>
                        <span id="userRole" class="topbar-role d-none">Rol</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow border-0">
                        <div class="dropdown-item-text small text-muted">
                            <strong id="userEmail">sin-correo</strong>
                        </div>
                        <div class="dropdown-divider"></div>
                        <button id="logoutBtn" class="dropdown-item">
                            <i class="mr-2 fas fa-sign-out-alt text-danger"></i> Cerrar sesion
                        </button>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar elevation-4">
            <a href="{{ url('/') }}" class="brand-link">
                <div class="brand-shell">
                    <img src="{{ asset('assets/dist/img/AdminLTELogo.png') }}" alt="HECAB Logo" class="brand-logo-full">
                    <div class="brand-copy">
                        <p class="brand-title">HECAB</p>
                        <div class="brand-subtitle">Sistema de gestion</div>
                    </div>
                </div>
            </a>

            <div class="sidebar">
                <div class="sidebar-user-card">
                    <small>Sesion activa</small>
                    <strong id="sidebarUserName">Invitado</strong>
                    <span id="sidebarUserEmail">sin-correo</span>
                    <div id="sidebarUserRole" class="sidebar-user-role d-none">Rol</div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        @foreach($menuSections as $section)
                            <li class="nav-header nav-section-label" data-section-label="{{ $section['key'] }}">{{ $section['label'] }}</li>
                            @foreach($section['items'] as $item)
                                @php
                                    $match = $item['match'] ?? ltrim($item['href'], '/');
                                    $hasChildren = !empty($item['children']);
                                    $isActive = $match === ''
                                        ? request()->path() === '/'
                                        : request()->is($match);
                                    $childActive = false;

                                    if ($hasChildren) {
                                        foreach ($item['children'] as $child) {
                                            $childMatch = $child['match'] ?? ltrim($child['href'], '/');
                                            if ($childMatch === '' ? request()->path() === '/' : request()->is($childMatch)) {
                                                $childActive = true;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <li class="nav-item {{ $hasChildren && $childActive ? 'menu-open' : '' }}"
                                    data-section-item="{{ $section['key'] }}"
                                    data-role-guard="{{ implode(',', $item['roles'] ?? []) }}"
                                    data-nav-path="{{ $item['href'] }}">
                                    <a href="{{ $hasChildren ? '#' : $item['href'] }}" class="nav-link {{ $isActive || $childActive ? 'active' : '' }}">
                                        <i class="nav-icon {{ $item['icon'] }}"></i>
                                        <p>
                                            {{ $item['text'] }}
                                            @if($hasChildren)
                                                <i class="right fas fa-angle-left"></i>
                                            @endif
                                        </p>
                                        @if(!$hasChildren && !empty($item['pill']))
                                            <span class="nav-pill">{{ $item['pill'] }}</span>
                                        @endif
                                    </a>
                                    @if($hasChildren)
                                        <ul class="nav nav-treeview">
                                            @foreach($item['children'] as $child)
                                                @php
                                                    $childMatch = $child['match'] ?? ltrim($child['href'], '/');
                                                    $childIsActive = $childMatch === ''
                                                        ? request()->path() === '/'
                                                        : request()->is($childMatch);
                                                @endphp
                                                <li class="nav-item">
                                                    <a href="{{ $child['href'] }}" class="nav-link {{ $childIsActive ? 'active' : '' }}">
                                                        <i class="nav-icon {{ $child['icon'] }}"></i>
                                                        <p>{{ $child['text'] }}</p>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <div class="content-header"></div>
            @yield('contenido')
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                HECAB · Operaciones, rutas y ventas
            </div>
            <strong>Copyright &copy; 2026 <a href="{{ url('/') }}">HECAB</a>.</strong> Todos los derechos reservados.
        </footer>
    </div>

    <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/jwtapi_v2.js') }}?v={{ filemtime(public_path('assets/js/jwtapi_v2.js')) }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        const ROLE_DEFINITIONS = @json($roleDefinitions);

        function getStoredUser() {
            const raw = localStorage.getItem('auth_user');

            if (!raw) {
                return null;
            }

            try {
                return JSON.parse(raw);
            } catch (error) {
                localStorage.removeItem('auth_user');
                return null;
            }
        }

        function setStoredUser(user) {
            localStorage.setItem('auth_user', JSON.stringify(user));
            localStorage.setItem('user', user.email || '');
        }

        function isPathAllowed(role, path) {
            const definition = ROLE_DEFINITIONS[role];

            if (!definition) {
                return false;
            }

            const allowedPaths = definition.paths || [];

            if (allowedPaths.includes('*')) {
                return true;
            }

            return allowedPaths.some((allowedPath) => {
                if (allowedPath === '/') {
                    return path === '/';
                }

                return path === allowedPath || path.startsWith(`${allowedPath}/`);
            });
        }

        function getFallbackPath(role) {
            const definition = ROLE_DEFINITIONS[role];

            if (!definition) {
                return '/';
            }

            if (definition.default_path) {
                return definition.default_path;
            }

            if (!Array.isArray(definition.paths) || definition.paths.length === 0 || definition.paths.includes('*')) {
                return '/';
            }

            return definition.paths.find((path) => path !== '/') || '/';
        }

        function applyUserToLayout(user) {
            const roleLabel = user.rol_label || ROLE_DEFINITIONS[user.rol]?.label || user.rol || 'Sin rol';

            document.getElementById('userName').textContent = user.name || 'Usuario';
            document.getElementById('userEmail').textContent = user.email || 'sin-correo';
            document.getElementById('sidebarUserName').textContent = user.name || 'Usuario';
            document.getElementById('sidebarUserEmail').textContent = user.email || 'sin-correo';

            const topRole = document.getElementById('userRole');
            const sideRole = document.getElementById('sidebarUserRole');

            topRole.textContent = roleLabel;
            sideRole.textContent = roleLabel;
            topRole.classList.remove('d-none');
            sideRole.classList.remove('d-none');
        }

        function filterMenuByRole(role) {
            document.querySelectorAll('[data-role-guard]').forEach((item) => {
                const roles = (item.dataset.roleGuard || '').split(',').filter(Boolean);
                const visible = roles.includes(role);
                item.classList.toggle('d-none', !visible);
            });

            document.querySelectorAll('[data-section-label]').forEach((label) => {
                const key = label.dataset.sectionLabel;
                const hasVisibleItems = Array.from(document.querySelectorAll(`[data-section-item="${key}"]`))
                    .some((item) => !item.classList.contains('d-none'));

                label.classList.toggle('d-none', !hasVisibleItems);
            });
        }

        async function ensureUserContext() {
            const token = localStorage.getItem('token');

            if (!token) {
                window.location.href = '/login';
                return null;
            }

            let user = getStoredUser();

            if (user && user.rol) {
                return user;
            }

            const response = await apiFetch('/api/me', {
                method: 'POST',
            });

            user = response.user;
            setStoredUser(user);

            return user;
        }

        document.addEventListener('DOMContentLoaded', async function () {
            try {
                const user = await ensureUserContext();

                if (!user) {
                    return;
                }

                applyUserToLayout(user);
                filterMenuByRole(user.rol);

                const currentPath = window.location.pathname;

                if (!isPathAllowed(user.rol, currentPath)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Acceso restringido',
                        text: 'Tu rol no tiene acceso a esta seccion.',
                        timer: 1800,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.href = getFallbackPath(user.rol);
                    });

                    return;
                }

                document.getElementById('logoutBtn').addEventListener('click', async () => {
                    try {
                        const response = await fetch('/api/logout', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        localStorage.removeItem('token');
                        localStorage.removeItem('user');
                        localStorage.removeItem('auth_user');

                        Swal.fire({
                            icon: response.ok ? 'success' : 'error',
                            title: response.ok ? 'Sesion cerrada' : 'Error',
                            text: data.message || data.error || 'No se pudo cerrar sesion',
                            timer: 1800,
                            showConfirmButton: false,
                        }).then(() => window.location.href = '/login');
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cerrar sesion',
                            timer: 1800,
                            showConfirmButton: false,
                        });
                    }
                });
            } catch (error) {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                localStorage.removeItem('auth_user');
                window.location.href = '/login';
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
