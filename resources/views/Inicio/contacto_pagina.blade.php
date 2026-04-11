@extends('Inicio.layout_publico')

@section('title', config('empresa.nombre_comercial') . ' | Contacto')

@section('content')
<section class="hero-page">
    <div class="container">
        <h1>Contacto comercial</h1>
        <p class="mb-0">Estamos listos para ayudarte con cotizaciones, soporte tecnico y coordinacion operativa.</p>
    </div>
</section>

@include('Inicio.contacto', ['empresa' => config('empresa'), 'anchorId' => 'contacto-principal'])
@endsection
