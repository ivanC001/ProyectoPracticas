<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('empresa.nombre_comercial') }} | Soluciones Energeticas</title>
    <meta name="description" content="Empresa especializada en servicios energeticos, instalacion, mantenimiento y operacion tecnica para clientes industriales y comerciales.">
    <link rel="icon" href="{{ asset('assets/dinamic/images/fevicon.png') }}" type="image/gif">
    <link rel="stylesheet" href="{{ asset('assets/dinamic/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Merriweather:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --hecab-blue: #0a3a72;
            --hecab-sky: #1b74d1;
            --hecab-accent: #f59e0b;
            --hecab-ink: #152033;
            --hecab-soft: #eef4fb;
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
            min-height: 84px;
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

        .btn-hecab {
            background: linear-gradient(120deg, var(--hecab-blue), var(--hecab-sky));
            color: #fff;
            border-radius: 30px;
            padding: 10px 22px;
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

        .hero {
            background:
                linear-gradient(rgba(8, 22, 45, 0.82), rgba(8, 22, 45, 0.78)),
                url('{{ asset('assets/dinamic/images/banner.jpg') }}') center/cover no-repeat;
            color: #fff;
            padding: 110px 0 90px;
        }

        .hero h1 {
            font-family: 'Merriweather', serif;
            font-size: 2.5rem;
            line-height: 1.3;
        }

        .hero p {
            color: #d3def0;
            font-size: 1.08rem;
            max-width: 620px;
        }

        .hero-kpi {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            padding: 14px 16px;
            margin-top: 18px;
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
            max-width: 700px;
            margin-bottom: 35px;
        }

        .service-card,
        .work-card,
        .contact-card {
            background: #fff;
            border: 1px solid #e3eaf5;
            border-radius: 16px;
            padding: 24px;
            height: 100%;
            box-shadow: 0 10px 24px rgba(20, 44, 86, 0.06);
        }

        .service-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #11458a, #1f7ae5);
            margin-bottom: 16px;
        }

        .work-card img {
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

        .contact-wrap {
            background: linear-gradient(180deg, #f4f8fe 0%, #e8f0fb 100%);
        }

        .contact-line {
            display: flex;
            align-items: flex-start;
            margin-bottom: 14px;
            color: #2f3f5f;
        }

        .contact-line i {
            width: 24px;
            margin-top: 2px;
            color: var(--hecab-blue);
        }

        footer {
            background: #0b1d38;
            color: #b9c8de;
            padding: 24px 0;
            font-size: 14px;
        }

        @media (max-width: 991.98px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero {
                padding: 90px 0 72px;
            }

            .navbar-light .navbar-nav .nav-link {
                margin-left: 0;
            }
        }
    </style>
</head>
@php
    $empresa = config('empresa');
    $telefono = (string) data_get($empresa, 'telefono', '');
    $whatsapp = preg_replace('/\D+/', '', $telefono);
    if (strlen($whatsapp) === 9) {
        $whatsapp = '51' . $whatsapp;
    }
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
            <a class="navbar-brand brand-dual" href="#inicio">
                <img src="{{ asset('assets/dinamic/images/logo.png') }}" alt="Logo HECAB" class="brand-logo">
                <span class="brand-copy">
                    {{ data_get($empresa, 'nombre_comercial') }}
                    <small>Servicios Energeticos</small>
                </span>
                
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#menuInicio" aria-controls="menuInicio" aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuInicio">
                <ul class="navbar-nav ml-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
                    <li class="nav-item"><a class="nav-link" href="#transporte">Transporte</a></li>
                    <li class="nav-item"><a class="nav-link" href="#productos">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#trabajos">Trabajos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#clientes">Cobertura</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contacto">Contactanos</a></li>
                    <li class="nav-item ml-lg-3 mt-2 mt-lg-0">
                        <a href="/login" class="btn btn-hecab">Portal interno</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero" id="inicio">
        <div class="container">
            <h1>Soluciones energeticas para operaciones industriales y comerciales</h1>
            <p class="mt-3 mb-4">
                En {{ data_get($empresa, 'razon_social') }}, ejecutamos trabajos tecnicos de campo con enfoque en seguridad, continuidad operativa y cumplimiento de plazos.
            </p>
            <div class="d-flex flex-wrap">
                <a href="/contacto" class="btn btn-hecab mr-2 mb-2">Contactanos</a>
                @if(!empty($whatsapp))
                    <a href="https://wa.me/{{ $whatsapp }}" target="_blank" class="btn btn-outline-light mb-2">Hablar por WhatsApp</a>
                @endif
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="hero-kpi"><strong>Atencion personalizada</strong><br><small>Soporte comercial y tecnico</small></div>
                </div>
                <div class="col-md-4">
                    <div class="hero-kpi"><strong>Equipo de campo</strong><br><small>Personal con experiencia operativa</small></div>
                </div>
                <div class="col-md-4">
                    <div class="hero-kpi"><strong>Facturacion formal</strong><br><small>Comprobantes electronicos SUNAT</small></div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-block" id="importancia">
        <div class="container">
            <h2 class="section-title">Informacion relevante para tu operacion</h2>
            <p class="section-lead">Priorizamos la continuidad de tus operaciones con cumplimiento tecnico, documentario y comercial.</p>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-clock"></i></div>
                        <h5>Respuesta rapida</h5>
                        <p>Atendemos requerimientos operativos y coordinaciones comerciales con seguimiento directo.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-file-contract"></i></div>
                        <h5>Documentacion en regla</h5>
                        <p>Gestion de cotizaciones, comprobantes y trazabilidad para auditorias internas.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-user-shield"></i></div>
                        <h5>Seguridad operativa</h5>
                        <p>Trabajos con enfoque en buenas practicas tecnicas y control operativo en campo.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-block" id="servicios">
        <div class="container">
            <h2 class="section-title">Servicios principales</h2>
            <p class="section-lead">Nos especializamos en ejecucion tecnica, mantenimiento y soporte para sistemas energeticos y operaciones asociadas.</p>
            <div class="row">
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-fire-burner"></i></div>
                        <h5>Instalacion GLP</h5>
                        <p>Montaje de tuberias, conexiones y pruebas operativas para sistemas de GLP.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                        <h5>Mantenimiento</h5>
                        <p>Mantenimiento preventivo y correctivo de equipos e instalaciones.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-truck-fast"></i></div>
                        <h5>Soporte operativo</h5>
                        <p>Coordinacion operativa para servicios de transporte y distribucion tecnica.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                        <h5>Comercial y facturacion</h5>
                        <p>Cotizaciones, facturacion electronica y seguimiento documentario para clientes.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-block pt-0" id="transporte">
        <div class="container">
            <h2 class="section-title">Transporte y operacion</h2>
            <p class="section-lead">Contamos con soporte para actividades de transporte de carga, programacion de rutas y control operativo.</p>
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-truck-fast"></i></div>
                        <h5>Transporte de carga</h5>
                        <p>Coordinacion de servicios de transporte y operacion tecnica en ruta.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-route"></i></div>
                        <h5>Planificacion de rutas</h5>
                        <p>Seguimiento de viajes, control de costos y apoyo documentario operativo.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                        <h5>Guias y soporte documental</h5>
                        <p>Asistencia en procesos de guias de remision y documentos relacionados.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-block pt-0" id="productos">
        <div class="container">
            <h2 class="section-title">Productos para grifos y operaciones</h2>
            <p class="section-lead">Tambien brindamos productos y componentes clave para estaciones y operaciones de despacho.</p>
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="work-card">
                        <img src="{{ asset('assets/dinamic/images/our.jpg') }}" alt="Producto para grifos">
                        <h5>Componentes de conexion</h5>
                        <p>Valvulas, mangueras, accesorios y elementos de conexion para sistemas de despacho.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="work-card">
                        <img src="{{ asset('assets/dinamic/images/about_img.png') }}" alt="Producto tecnico">
                        <h5>Equipamiento de apoyo</h5>
                        <p>Material tecnico para mantenimiento y soporte en campo.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="work-card">
                        <img src="{{ asset('assets/dinamic/images/mobile.png') }}" alt="Producto especializado">
                        <h5>Soluciones por proyecto</h5>
                        <p>Suministro segun necesidad operativa y alcance tecnico del cliente.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-block pt-0" id="trabajos">
        <div class="container">
            <h2 class="section-title">Trabajos y alcances</h2>
            <p class="section-lead">Estos son algunos tipos de trabajo que ejecutamos con frecuencia para nuestros clientes.</p>
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="work-card">
                        <img src="{{ asset('assets/dinamic/images/our.jpg') }}" alt="Instalaciones">
                        <h5>Instalacion de lineas GLP</h5>
                        <p>Implementacion de lineas de impulsion y retorno, accesorios, valvulas y pruebas de hermeticidad.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="work-card">
                        <img src="{{ asset('assets/dinamic/images/about_img.png') }}" alt="Mantenimiento">
                        <h5>Puesta en marcha y calibracion</h5>
                        <p>Configuracion de dispensadores, conexion electrica y validacion de funcionamiento en campo.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="work-card">
                        <img src="{{ asset('assets/dinamic/images/mobile.png') }}" alt="Soporte">
                        <h5>Servicios empresariales</h5>
                        <p>Atencion a operaciones de transporte y soporte tecnico para servicios gravados con IGV.</p>
                        <a href="/contacto" class="btn btn-contactanos btn-sm">Contactanos</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-block pt-0" id="clientes">
        <div class="container">
            <h2 class="section-title">Cobertura y sectores</h2>
            <p class="section-lead">Atendemos clientes de distintos sectores, con enfoque en continuidad operativa y respuesta tecnica oportuna.</p>
            <div>
                <span class="chip">Estaciones de servicio</span>
                <span class="chip">Transporte de carga</span>
                <span class="chip">Industria y manufactura</span>
                <span class="chip">Comercio mayorista</span>
                <span class="chip">Callao y Lima</span>
                <span class="chip">Atencion programada y urgente</span>
            </div>
            <div class="mt-3">
                <a href="/contacto" class="btn btn-hecab">Contactanos</a>
            </div>
        </div>
    </section>

    @include('Inicio.contacto', ['empresa' => $empresa, 'anchorId' => 'contacto'])

    <footer>
        <div class="container d-flex flex-column flex-md-row justify-content-between">
            <div>{{ data_get($empresa, 'nombre_comercial') }} - {{ date('Y') }}</div>
            <div>Gerencia: {{ data_get($empresa, 'gerente_nombre') }}</div>
        </div>
    </footer>

    <script src="{{ asset('assets/dinamic/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/dinamic/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
