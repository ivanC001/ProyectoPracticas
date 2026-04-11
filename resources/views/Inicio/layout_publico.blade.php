<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('empresa.nombre_comercial') . ' | Soluciones Energeticas')</title>
    <meta name="description" content="@yield('meta_description', 'Empresa especializada en servicios energeticos y operaciones tecnicas para clientes industriales y comerciales.')">
    <link rel="icon" href="{{ asset('assets/dinamic/images/fevicon.png') }}" type="image/gif">
    <link rel="stylesheet" href="{{ asset('assets/dinamic/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Merriweather:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --hecab-blue: #0a3a72;
            --hecab-sky: #1b74d1;
            --hecab-ink: #152033;
        }

        body {
            font-family: 'Manrope', sans-serif;
            color: var(--hecab-ink);
            background: #f7f9fc;
        }

        .topbar {
            background: #081a33;
            color: #d8e4f5;
            font-size: 14px;
            padding: 8px 0;
        }

        .topbar a {
            color: #d8e4f5;
            text-decoration: none;
        }

        .topbar a:hover {
            color: #fff;
        }

        .main-nav {
            min-height: 86px;
            box-shadow: 0 10px 30px rgba(13, 42, 81, 0.08);
        }

        .brand-dual {
            display: flex;
            align-items: center;
            gap: 14px;
            font-weight: 800;
            color: #163764;
            line-height: 1.1;
        }

        .brand-logo {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }

        .brand-copy small {
            display: block;
            font-size: 12px;
            color: #4f5f79;
            font-weight: 700;
        }

        .navbar-light .navbar-nav .nav-link {
            font-weight: 800;
            color: #1d3557;
            margin-left: 16px;
            font-size: 1rem;
            padding: 12px 4px;
        }

        .navbar-light .navbar-nav .nav-link.active {
            color: #0a3a72;
            border-bottom: 2px solid #1b74d1;
        }

        .btn-hecab {
            background: linear-gradient(120deg, var(--hecab-blue), var(--hecab-sky));
            color: #fff;
            border-radius: 30px;
            padding: 11px 24px;
            font-weight: 700;
            border: 0;
        }

        .btn-hecab:hover {
            color: #fff;
            opacity: 0.95;
        }

        .btn-contactanos {
            border-radius: 24px;
            font-weight: 700;
            padding: 8px 16px;
            border: 1px solid #1b74d1;
            color: #1b74d1;
            background: #fff;
        }

        .btn-contactanos:hover {
            background: #1b74d1;
            color: #fff;
        }

        .hero-page {
            background:
                linear-gradient(rgba(8, 22, 45, 0.84), rgba(8, 22, 45, 0.78)),
                url('{{ asset('assets/dinamic/images/banner.jpg') }}') center/cover no-repeat;
            color: #fff;
            padding: 88px 0 70px;
        }

        .hero-page h1 {
            font-family: 'Merriweather', serif;
            margin-bottom: 12px;
        }

        .section-block {
            padding: 72px 0;
        }

        .section-title {
            font-family: 'Merriweather', serif;
            color: #12284a;
            margin-bottom: 14px;
        }

        .section-lead {
            color: #4f5f79;
            max-width: 760px;
            margin-bottom: 35px;
        }

        .card-public {
            background: #fff;
            border: 1px solid #e3eaf5;
            border-radius: 16px;
            padding: 24px;
            height: 100%;
            box-shadow: 0 10px 24px rgba(20, 44, 86, 0.06);
        }

        .card-public img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .chip {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #e6effd;
            color: #15407b;
            font-weight: 700;
            font-size: 13px;
            margin: 0 8px 8px 0;
        }

        footer {
            background: #0b1d38;
            color: #b9c8de;
            padding: 24px 0;
            font-size: 14px;
        }

        @media (max-width: 991.98px) {
            .navbar-light .navbar-nav .nav-link {
                margin-left: 0;
            }
        }
    </style>
    @stack('head')
</head>
@php
    $empresa = config('empresa');
    $telefono = (string) data_get($empresa, 'telefono', '');
@endphp
<body>
    <div class="topbar">
        <div class="container d-flex flex-column flex-md-row justify-content-between">
            <div><i class="fa-solid fa-location-dot mr-2"></i>{{ data_get($empresa, 'direccion') }}</div>
            <div class="mt-1 mt-md-0">
                <i class="fa-solid fa-phone mr-2"></i><a href="tel:{{ preg_replace('/\D+/', '', $telefono) }}">{{ $telefono }}</a>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top main-nav">
        <div class="container">
            <a class="navbar-brand brand-dual" href="{{ route('public.inicio') }}">
                <img src="{{ asset('assets/dinamic/images/logo.png') }}" alt="Logo HECAB" class="brand-logo">
                <span class="brand-copy">
                    {{ data_get($empresa, 'nombre_comercial') }}
                    <small>Servicios Energeticos</small>
                </span>
                <img src="{{ asset('assets/dinamic/images/logo1.png') }}" alt="Logo secundario HECAB" class="brand-logo">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#menuInicio" aria-controls="menuInicio" aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuInicio">
                <ul class="navbar-nav ml-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.inicio') ? 'active' : '' }}" href="{{ route('public.inicio') }}">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.servicios') ? 'active' : '' }}" href="{{ route('public.servicios') }}">Servicios</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.transporte') ? 'active' : '' }}" href="{{ route('public.transporte') }}">Transporte</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.productos') ? 'active' : '' }}" href="{{ route('public.productos') }}">Productos</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.trabajos') ? 'active' : '' }}" href="{{ route('public.trabajos') }}">Trabajos</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.cobertura') ? 'active' : '' }}" href="{{ route('public.cobertura') }}">Cobertura</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('public.contacto') ? 'active' : '' }}" href="{{ route('public.contacto') }}">Contactanos</a></li>
                    <li class="nav-item ml-lg-3 mt-2 mt-lg-0">
                        <a href="/login" class="btn btn-hecab">Portal interno</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer>
        <div class="container d-flex flex-column flex-md-row justify-content-between">
            <div>{{ data_get($empresa, 'nombre_comercial') }} - {{ date('Y') }}</div>
            <div>Gerencia: {{ data_get($empresa, 'gerente_nombre') }}</div>
        </div>
    </footer>

    <script src="{{ asset('assets/dinamic/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/dinamic/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
