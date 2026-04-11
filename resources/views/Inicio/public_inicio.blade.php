@extends('Inicio.layout_publico')

@section('title', config('empresa.nombre_comercial') . ' | Inicio')

@php
    $empresa = config('empresa');
    $telefono = (string) data_get($empresa, 'telefono', '');
    $whatsapp = preg_replace('/\D+/', '', $telefono);
    if (strlen($whatsapp) === 9) {
        $whatsapp = '51' . $whatsapp;
    }
@endphp

@section('content')
<section class="hero-page">
    <div class="container">
        <h1>Soluciones energeticas para operaciones industriales y comerciales</h1>
        <p class="mb-4">En {{ data_get($empresa, 'razon_social') }}, ejecutamos trabajos tecnicos de campo con enfoque en seguridad, continuidad operativa y cumplimiento de plazos.</p>
        <a href="{{ route('public.contacto') }}" class="btn btn-hecab mr-2 mb-2">Contactanos</a>
        @if(!empty($whatsapp))
            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" class="btn btn-outline-light mb-2">Hablar por WhatsApp</a>
        @endif
    </div>
</section>

<section class="section-block">
    <div class="container">
        <h2 class="section-title">Informacion relevante</h2>
        <p class="section-lead">Priorizamos la continuidad de tus operaciones con cumplimiento tecnico, documentario y comercial.</p>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card-public">
                    <h5>Respuesta rapida</h5>
                    <p>Atendemos requerimientos operativos y coordinaciones comerciales con seguimiento directo.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card-public">
                    <h5>Documentacion en regla</h5>
                    <p>Gestion de cotizaciones, comprobantes y trazabilidad para auditorias internas.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card-public">
                    <h5>Seguridad operativa</h5>
                    <p>Trabajos con enfoque en buenas practicas tecnicas y control operativo en campo.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
