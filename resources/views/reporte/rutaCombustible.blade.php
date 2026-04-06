@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <h3 class="font-weight-bold mb-3">Redirigiendo al detalle de combustible...</h3>
                <p class="text-muted mb-4">Esta vista antigua ahora forma parte del reporte completo de la ruta.</p>
                <a href="/reportes/rutas/{{ $id }}#combustible" class="btn btn-primary">
                    Ir ahora
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    window.location.replace('/reportes/rutas/{{ $id }}#combustible');
});
</script>
@endpush
