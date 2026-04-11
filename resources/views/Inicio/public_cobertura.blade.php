@extends('Inicio.layout_publico')

@section('title', config('empresa.nombre_comercial') . ' | Cobertura')

@section('content')
<section class="hero-page">
    <div class="container">
        <h1>Cobertura y sectores atendidos</h1>
        <p class="mb-0">Atencion para distintos sectores con enfoque en continuidad operativa y respuesta tecnica.</p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <h2 class="section-title">Sectores y alcance</h2>
        <p class="section-lead">Trabajamos con clientes de diferentes rubros segun necesidades de campo y operacion.</p>
        <div class="mb-4">
            <span class="chip">Estaciones de servicio</span>
            <span class="chip">Transporte de carga</span>
            <span class="chip">Industria y manufactura</span>
            <span class="chip">Comercio mayorista</span>
            <span class="chip">Callao y Lima</span>
            <span class="chip">Atencion programada y urgente</span>
        </div>
        <a href="{{ route('public.contacto') }}" class="btn btn-hecab">Contactanos</a>
    </div>
</section>
@endsection
