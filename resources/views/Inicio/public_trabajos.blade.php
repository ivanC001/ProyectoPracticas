@extends('Inicio.layout_publico')

@section('title', config('empresa.nombre_comercial') . ' | Trabajos')

@section('content')
<section class="hero-page">
    <div class="container">
        <h1>Trabajos y alcances</h1>
        <p class="mb-0">Proyectos ejecutados con enfoque tecnico, seguridad y cumplimiento operativo.</p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <h2 class="section-title">Tipos de trabajos frecuentes</h2>
        <p class="section-lead">Este contenido es referencial y puede ajustarse segun cada requerimiento.</p>
        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-public">
                    <img src="{{ asset('assets/dinamic/images/our1.jpg') }}" alt="Instalaciones">
                    <h5>Instalacion de lineas GLP</h5>
                    <p>Lineas de impulsion y retorno, accesorios y pruebas de hermeticidad.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-public">
                    <img src="{{ asset('assets/dinamic/images/about_img.png') }}" alt="Puesta en marcha">
                    <h5>Puesta en marcha y calibracion</h5>
                    <p>Configuracion de dispensadores y validacion de funcionamiento.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-public">
                    <img src="{{ asset('assets/dinamic/images/banner.jpg') }}" alt="Servicios tecnicos">
                    <h5>Servicios para estaciones</h5>
                    <p>Atencion tecnica para operaciones en grifos y soporte empresarial.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
