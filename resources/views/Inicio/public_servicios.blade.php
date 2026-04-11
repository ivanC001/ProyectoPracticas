@extends('Inicio.layout_publico')

@section('title', config('empresa.nombre_comercial') . ' | Servicios')

@section('content')
<section class="hero-page">
    <div class="container">
        <h1>Servicios especializados</h1>
        <p class="mb-0">Ejecucion tecnica, mantenimiento y soporte para sistemas energeticos y operaciones asociadas.</p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <h2 class="section-title">Nuestros servicios</h2>
        <p class="section-lead">Trabajamos con enfoque en calidad operativa y cumplimiento de tiempos.</p>
        <div class="row">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card-public">
                    <h5>Instalacion GLP</h5>
                    <p>Montaje de tuberias, conexiones y pruebas operativas para sistemas de GLP.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card-public">
                    <h5>Mantenimiento tecnico</h5>
                    <p>Mantenimiento preventivo y correctivo de equipos e instalaciones.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card-public">
                    <h5>Soporte operativo</h5>
                    <p>Atencion tecnica para operaciones de campo y soporte empresarial.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card-public">
                    <h5>Cotizacion y facturacion</h5>
                    <p>Gestion integral de documentos comerciales con respuesta oportuna.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
