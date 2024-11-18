@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">
                            Facturación 
                            <button class="btn btn-primary" onclick="window.location.href='{{ route('nueva-venta') }}'">
                                <i class="fas fa-file-invoice"></i> Nueva Factura
                            </button>
                            
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipo de Comprobante</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Monto Total</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody id="facturaTableBody">
                                    {{-- Aquí se llenará dinámicamente la tabla con las facturas existentes --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- @include('factura.registro') Modal para registrar nueva factura --}}

@endsection
