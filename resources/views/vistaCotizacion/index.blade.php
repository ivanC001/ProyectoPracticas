@extends('admin.main')

@section('contenido')

<div class="content">
    <div class="container-fluid">

        <div class="card shadow">

            <div class="card-header d-flex justify-content-between">
                <h5><i class="fas fa-file-invoice"></i> Cotizaciones</h5>

                <button class="btn btn-primary" data-toggle="modal" data-target="#modalCotizacion">
                    <i class="fas fa-plus"></i> Nueva Cotización
                </button>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCotizaciones"></tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- 🔥 MODAL -->
<div class="modal fade" id="modalCotizacion">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5>Nueva Cotización</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <!-- CLIENTE -->
                <div class="form-group">
                    <label>Cliente</label>
                    <select id="cliente_id" class="form-control"></select>
                </div>

                <!-- ITEMS -->
                <h6>Agregar Item</h6>

                <div class="row">
                    <div class="col-md-3">
                        <select id="tipo" class="form-control">
                            <option value="producto">Producto</option>
                            <option value="servicio">Servicio</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select id="item_select" class="form-control"></select>
                    </div>

                    <div class="col-md-2">
                        <input type="number" id="cantidad" class="form-control" placeholder="Cantidad">
                    </div>

                    <div class="col-md-2">
                        <input type="number" id="precio" class="form-control" placeholder="Precio (solo servicio)">
                    </div>

                    <div class="col-md-1">
                        <button class="btn btn-success" onclick="agregarItem()">+</button>
                    </div>
                </div>

                <hr>

                <!-- DETALLE -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>ID</th>
                            <th>Cant</th>
                            <th>Precio</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="detalleCotizacion"></tbody>
                </table>

                <h5>Total: S/ <span id="total">0.00</span></h5>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" onclick="guardarCotizacion()">Guardar</button>
                <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>

let items = [];

$(document).ready(function(){
    fetchCotizaciones();
    fetchClientes();
});

/* 🔥 LISTAR COTIZACIONES */
function fetchCotizaciones(){
    $.get('/api/cotizaciones', function(res){

        let tbody = $("#tablaCotizaciones");
        tbody.empty();

        res.forEach(c=>{
            tbody.append(`
                <tr>
                    <td>${c.id}</td>
                    <td>${c.cliente?.razon_social ?? ''}</td>
                    <td>${c.fecha}</td>
                    <td>S/ ${c.total}</td>
                    <td>${c.estado}</td>
                </tr>
            `);
        });
    });
}

/* 🔥 CLIENTES */
function fetchClientes(){
    $.get('/api/clientes', function(res){

        let select = $("#cliente_id");
        select.empty();

        res.data.forEach(c=>{
            select.append(`<option value="${c.id}">${c.razon_social}</option>`);
        });
    });
}

/* 🔥 AGREGAR ITEM */
function agregarItem(){

    let tipo = $('#tipo').val();
    let id = $('#item_select').val();
    let cantidad = parseFloat($('#cantidad').val());
    let precio = parseFloat($('#precio').val());

    if(!cantidad){
        Swal.fire('Error','Cantidad inválida','error');
        return;
    }

    let item = {
        tipo: tipo,
        cantidad: cantidad
    };

    if(tipo === 'producto'){
        item.producto_id = id;
    }else{
        item.servicio_id = id;
        item.precio = precio;
    }

    items.push(item);
    renderItems();
}

/* 🔥 RENDER */
function renderItems(){

    let tbody = $("#detalleCotizacion");
    let total = 0;

    tbody.empty();

    items.forEach((i,index)=>{

        let sub = i.cantidad * (i.precio ?? 0);
        total += sub;

        tbody.append(`
            <tr>
                <td>${i.tipo}</td>
                <td>${i.producto_id ?? i.servicio_id}</td>
                <td>${i.cantidad}</td>
                <td>${i.precio ?? '-'}</td>
                <td><button class="btn btn-danger btn-sm" onclick="eliminarItem(${index})">X</button></td>
            </tr>
        `);
    });

    $('#total').text(total.toFixed(2));
}

/* 🔥 ELIMINAR ITEM */
function eliminarItem(index){
    items.splice(index,1);
    renderItems();
}

/* 🔥 GUARDAR */
function guardarCotizacion(){

    let data = {
        cliente_id: $('#cliente_id').val(),
        items: items
    };

    $.ajax({
        url: '/api/cotizaciones',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(resp){

            if(resp.success){
                Swal.fire('OK', resp.message, 'success');

                $('#modalCotizacion').modal('hide');

                items = [];
                renderItems();

                fetchCotizaciones();
            }else{
                Swal.fire('Error', resp.message, 'error');
            }
        },
        error: function(){
            Swal.fire('Error','Error en el servidor','error');
        }
    });
}

</script>
@endpush