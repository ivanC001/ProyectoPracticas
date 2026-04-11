@extends('Inicio.layout_publico')

@section('title', config('empresa.nombre_comercial') . ' | Transporte')

@section('content')
<section class="hero-page">
    <div class="container">
        <h1>Transporte y operacion</h1>
        <p class="mb-0">Soporte para actividades de transporte de carga, planificacion de rutas y control operativo.</p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <h2 class="section-title">Lineas de trabajo en transporte</h2>
        <p class="section-lead">Ayudamos a mantener tus operaciones en movimiento con trazabilidad y soporte documental.</p>
        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-public">
                    <h5>Transporte de carga</h5>
                    <p>Coordinacion de servicios de transporte y operacion tecnica en ruta.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-public">
                    <h5>Planificacion de rutas</h5>
                    <p>Seguimiento de viajes, control de costos y apoyo documentario operativo.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-public">
                    <h5>Guias y documentos</h5>
                    <p>Asistencia en procesos de guias de remision y documentacion relacionada.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
