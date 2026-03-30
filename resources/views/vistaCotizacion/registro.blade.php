@extends('admin.main')

@section('contenido')
<div class="content">
<div class="container-fluid">

<div class="row">

<!-- 🟦 IZQUIERDA -->
<div class="col-md-8">

<div class="card shadow-sm">
<div class="card-header">
    <strong>Agregar Producto / Servicio</strong>
</div>

<div class="card-body">

<div class="row mb-3">
    <div class="col-md-4">
        <select id="tipo" class="form-control">
            <option value="producto">Producto</option>
            <option value="servicio">Servicio</option>
        </select>
    </div>

    <div class="col-md-8">
        <input type="text" id="buscar"
               class="form-control"
               placeholder="Buscar...">
    </div>
</div>

<div id="resultados"></div>

<hr>

<table class="table table-bordered text-center">
<thead>
<tr>
<th>Tipo</th>
<th>Detalle</th>
<th>Cant</th>
<th>Precio</th>
<th>Sub</th>
<th></th>
</tr>
</thead>
<tbody id="itemsTable"></tbody>
</table>

</div>
</div>

</div>

<!-- 🟩 DERECHA -->
<div class="col-md-4">

<div class="card shadow-sm">
<div class="card-header">
    <strong>Resumen</strong>
</div>

<div class="card-body">

<label>Cliente *</label>
<select id="cliente_id" class="form-control mb-3"></select>

<hr>

<p>Subtotal: S/ <span id="subtotal">0.00</span></p>
<p>IGV: S/ <span id="igv">0.00</span></p>
<h4>Total: S/ <span id="total">0.00</span></h4>

<button class="btn btn-success btn-block mt-3"
        onclick="guardar()">
    Guardar
</button>

</div>
</div>

</div>

</div>

</div>
</div>
@endsection
@push('scripts')
<script>

let productos = [];
let servicios = [];
let clientes = [];
let items = [];

/* INIT */
async function init(){

    let p = await apiFetch('/api/productos');
    let s = await apiFetch('/api/servicios');
    let c = await apiFetch('/api/clientes');

    productos = p.data;
    servicios = s.data;
    clientes = c.data;

    let html = '<option value="">Seleccione</option>';
    clientes.forEach(x=>{
        html += `<option value="${x.id}">${x.razon_social}</option>`;
    });

    $('#cliente_id').html(html);
}

/* BUSCAR */
$('#buscar').on('input', function(){

    let txt = $(this).val().toLowerCase();
    let tipo = $('#tipo').val();

    let lista = tipo==='producto'?productos:servicios;

    let html='';

    lista.forEach(x=>{

        let nombre = x.descripcion || x.nombre;

        if(nombre.toLowerCase().includes(txt)){

            html += `
            <div class="border p-2 mb-1">

                <strong>${nombre}</strong><br>
                <small>S/ ${x.precio}</small>

                <button class="btn btn-sm btn-primary mt-1"
                    onclick="add('${tipo}',${x.id})">
                    Agregar
                </button>

            </div>`;
        }

    });

    $('#resultados').html(html);
});

/* AGREGAR */
function add(tipo,id){

    let data = tipo==='producto'
        ? productos.find(x=>x.id==id)
        : servicios.find(x=>x.id==id);

    items.push({
        tipo,
        id,
        nombre: data.descripcion || data.nombre,
        precio: data.precio,
        cantidad:1
    });

    render();
}

/* RENDER */
function render(){

    let html='';

    items.forEach((i,index)=>{

        html+=`
        <tr>

            <td>${i.tipo}</td>
            <td>${i.nombre}</td>

            <td>
                <input type="number" value="${i.cantidad}"
                    onchange="items[${index}].cantidad=this.value;calc()">
            </td>

            <td>${i.precio}</td>

            <td>${(i.precio*i.cantidad).toFixed(2)}</td>

            <td>
                <button onclick="items.splice(${index},1);render()">X</button>
            </td>

        </tr>`;
    });

    $('#itemsTable').html(html);

    calc();
}

/* CALCULAR */
function calc(){

    let sub=0;

    items.forEach(i=>{
        sub+=i.precio*i.cantidad;
    });

    let igv=sub*0.18;

    $('#subtotal').text(sub.toFixed(2));
    $('#igv').text(igv.toFixed(2));
    $('#total').text((sub+igv).toFixed(2));
}

/* GUARDAR */
function guardar(){

    apiFetch('/api/cotizaciones',{
        method:'POST',
        body: JSON.stringify({
            cliente_id: $('#cliente_id').val(),
            items: items.map(i=>({
                tipo: i.tipo,
                cantidad: i.cantidad,
                producto_id: i.tipo==='producto'?i.id:null,
                servicio_id: i.tipo==='servicio'?i.id:null
            }))
        })
    }).then(()=>{
        window.location='/cotizaciones';
    });

}

$(document).ready(init);

</script>
@endpush