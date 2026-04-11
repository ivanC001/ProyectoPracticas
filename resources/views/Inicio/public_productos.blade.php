@extends('Inicio.layout_publico')

@section('title', config('empresa.nombre_comercial') . ' | Productos')

@section('content')
<section class="hero-page">
    <div class="container">
        <h1>Productos para grifos y operaciones</h1>
        <p class="mb-0">Suministro de productos y componentes clave para estaciones y servicios de campo.</p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <h2 class="section-title">Catalogo orientativo</h2>
        <p class="section-lead">Ofrecemos soluciones por proyecto segun necesidad operativa de cada cliente.</p>
        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-public">
                    <img src="{{ asset('assets/dinamic/images/our.jpg') }}" alt="Producto para grifos">
                    <h5>Componentes de conexion</h5>
                    <p>Valvulas, mangueras y accesorios para sistemas de despacho.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-public">
                    <img src="{{ asset('assets/dinamic/images/about_img.png') }}" alt="Producto tecnico">
                    <h5>Equipamiento de apoyo</h5>
                    <p>Material tecnico para mantenimiento y soporte en campo.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card-public">
                    <img src="{{ asset('assets/dinamic/images/mobile.png') }}" alt="Producto especializado">
                    <h5>Soluciones por proyecto</h5>
                    <p>Productos y abastecimiento segun alcance tecnico requerido.</p>
                    <a href="{{ route('public.contacto') }}" class="btn btn-contactanos btn-sm">Contactanos</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
