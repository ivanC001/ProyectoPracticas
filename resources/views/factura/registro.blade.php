@extends('admin.main')

@section('contenido')

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">Registrar Factura</h5>
                    </div>
                    <div class="card-body">
                        <form id="formRegistroFactura">
                            @csrf
                            <input type="hidden" name="_method" value="POST">

                            <!-- Selección del Tipo de Documento del Cliente y Número de Documento en la misma línea -->
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="cliente_tipo_doc">Tipo de Documento del Cliente</label>
                                    <select class="form-control" id="cliente_tipo_doc" name="cliente_tipo_doc">
                                        <option value="0">Sin Datos</option>
                                        <option value="1">DNI</option>
                                        <option value="4">Carnet de extranjería</option>
                                        <option value="6">RUC</option>
                                        <option value="7">Pasaporte</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="cliente_num_doc">Número de Documento</label>
                                    <input type="text" class="form-control" id="cliente_num_doc" name="cliente_num_doc" required>
                                </div>
                                <!-- Razón Social del Cliente -->
                                <div class="form-group col-md-4">
                                    <label for="cliente_razon_social">Razón Social</label>
                                    <input type="text" class="form-control" id="cliente_razon_social" name="cliente_razon_social" required>
                                </div>
                            </div>

                            <!-- Selección de Moneda -->
                            <div class="form-group">
                                <label for="moneda">Moneda</label>
                                <select class="form-control" id="moneda" name="moneda">
                                    <option value="PEN">PEN - Soles</option>
                                    <option value="USD">USD - Dólares</option>
                                </select>
                            </div>

                            <!-- Selección de Productos -->
                            <div class="form-group">
                                <label for="productos">Seleccione Productos</label>
                                <select class="form-control" id="productos" name="productos"></select>
                            </div>

                            <!-- Cantidad y Total -->
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="cantidadProducto">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidadProducto" min="1" placeholder="Cantidad">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="precioTotalProducto">Total por Producto</label>
                                    <input type="text" class="form-control" id="precioTotalProducto" placeholder="S/ 0.00" readonly>
                                </div>
                            </div>

                            <button type="button" class="btn btn-secondary mt-2" onclick="agregarProducto()">Agregar Producto</button>

                            <!-- Lista de Productos Seleccionados -->
                            <div class="form-group mt-4">
                                <label>Productos Seleccionados</label>
                                <ul class="list-group" id="listaProductosSeleccionados"></ul>
                            </div>

                            <!-- Div para mostrar el total general -->
                            <div class="form-group mt-4 d-flex justify-content-end">
                                <h4>Total General: <span id="totalGeneral" class="text-primary">S/ 0.00</span></h4>
                            </div>                            

                            <button type="submit" class="btn btn-primary">Registrar Factura</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{asset('assets/js/facturajs/factura.js')}}"></script>
@endpush
