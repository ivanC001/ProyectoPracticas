<form id="formRegistroFactura">

@csrf

<!-- DATOS -->
<div class="row">
    <div class="col-md-4">
        <label>Tipo</label>
        <select class="form-control" id="tipo_documento">
            <option value="01">Factura</option>
            <option value="03">Boleta</option>
        </select>
    </div>

    <div class="col-md-4">
        <label>Fecha</label>
        <input type="datetime-local" class="form-control" id="fecha_emision">
    </div>

    <div class="col-md-4">
        <label>Moneda</label>
        <select class="form-control" id="moneda">
            <option value="PEN">Soles</option>
            <option value="USD">Dólares</option>
        </select>
    </div>
</div>

<!-- CLIENTE -->
<hr>
<h6 class="text-primary">Datos del Cliente</h6>

<div class="row">
    <div class="col-md-2">
        <select class="form-control" id="cliente_tipo_doc">
            <option value="1">DNI</option>
            <option value="6">RUC</option>
        </select>
    </div>

    <div class="col-md-3">
        <input type="text" class="form-control" id="cliente_num_doc" placeholder="Documento">
        <small id="clienteEstado"></small>
    </div>

    <div class="col-md-7">
        <input type="text" class="form-control" id="cliente_razon_social" placeholder="Nombre / Razón Social">
    </div>
</div>

<br>

<div class="row">
    <div class="col-md-4">
        <input type="text" class="form-control" id="cliente_direccion" placeholder="Dirección">
    </div>
    <div class="col-md-4">
        <input type="email" class="form-control" id="cliente_email" placeholder="Email">
    </div>
    <div class="col-md-4">
        <input type="text" class="form-control" id="cliente_telefono" placeholder="Teléfono">
    </div>
</div>

<hr>

<!-- PRODUCTO -->
<div class="row">
    <div class="col-md-4">
        <select class="form-control" id="productos"></select>
    </div>

    <div class="col-md-2">
        <input type="number" class="form-control" id="cantidadProducto" placeholder="Cantidad">
    </div>

    <div class="col-md-2">
        <input type="number" class="form-control" id="descuentoProducto" value="0">
    </div>

    <div class="col-md-2">
        <input type="text" class="form-control" id="precioUnitario" readonly>
    </div>

    <div class="col-md-2">
        <button type="button" class="btn btn-success btn-block" onclick="agregarProducto()">+</button>
    </div>
</div>

<br>

<table class="table table-bordered table-sm">
    <thead class="thead-dark">
        <tr>
            <th>Producto</th>
            <th>Cant</th>
            <th>Precio</th>
            <th>Desc</th>
            <th>Subtotal</th>
            <th></th>
        </tr>
    </thead>
    <tbody id="tablaProductos"></tbody>
</table>

<div class="text-right">
    <h5>Total: <span id="totalGeneral">S/ 0.00</span></h5>
</div>

</form>
@push('scripts')
<script>

let productosSeleccionados = [];
let timeoutCliente = null;

/* INIT */
document.addEventListener('DOMContentLoaded', function(){

    setFechaActual();
    fetchProductos();

    document.getElementById('productos').addEventListener('change', function(){
        let opt = this.options[this.selectedIndex];
        if(!opt.value) return;
        document.getElementById('precioUnitario').value = opt.dataset.precio;
    });

    document.getElementById('cliente_num_doc').addEventListener('keyup', function(){

        let doc = this.value.trim();

        clearTimeout(timeoutCliente);

        if(doc.length < 8) return;

        timeoutCliente = setTimeout(() => {
            buscarCliente(doc);
        }, 500);
    });

});


/* FECHA */
function setFechaActual(){
    let now = new Date();
    let offset = now.getTimezoneOffset();
    let local = new Date(now.getTime() - offset*60000);
    document.getElementById('fecha_emision').value = local.toISOString().slice(0,16);
}


/* PRODUCTOS */
function fetchProductos(){

    fetch('/api/productos')
    .then(r=>r.json())
    .then(data=>{

        let select = document.getElementById('productos');
        select.innerHTML = "<option value=''>Seleccione</option>";

        data.data.forEach(p=>{

            let opt = document.createElement('option');

            opt.value = p.codigo;
            opt.text = `${p.descripcion} (Stock: ${p.stock})`;

            opt.dataset.precio = p.precio;
            opt.dataset.stock = p.stock;
            opt.dataset.descripcion = p.descripcion;
            opt.dataset.unidad = p.unidad;

            if(p.stock <= 0) opt.disabled = true;

            select.appendChild(opt);
        });

    });
}


/* AGREGAR PRODUCTO */
function agregarProducto(){

    let select = document.getElementById('productos');
    let opt = select.options[select.selectedIndex];

    if(!opt.value){
        Swal.fire('Error','Seleccione producto','error');
        return;
    }

    let cantidad = parseFloat(document.getElementById('cantidadProducto').value);
    let descuento = parseFloat(document.getElementById('descuentoProducto').value) || 0;
    let precio = parseFloat(opt.dataset.precio);
    let stock = parseFloat(opt.dataset.stock);

    if(!cantidad || cantidad <= 0){
        Swal.fire('Error','Cantidad inválida','error');
        return;
    }

    if(cantidad > stock){
        Swal.fire('Error','Stock insuficiente','error');
        return;
    }

    let existente = productosSeleccionados.find(p => p.codigo === opt.value);

    if(existente){
        existente.cantidad += cantidad;
        existente.descuento += descuento;
    }else{
        productosSeleccionados.push({
            codigo: opt.value,
            descripcion: opt.dataset.descripcion,
            unidad: opt.dataset.unidad || 'NIU',
            cantidad: cantidad,
            valor_unitario: precio,
            descuento: descuento
        });
    }

    actualizarTabla();
}


/* TABLA */
function actualizarTabla(){

    let tbody = document.getElementById('tablaProductos');
    let total = 0;

    tbody.innerHTML = '';

    productosSeleccionados.forEach((p,index)=>{

        let subtotal = (p.cantidad * p.valor_unitario) - p.descuento;
        total += subtotal;

        tbody.innerHTML += `
            <tr>
                <td>${p.descripcion}</td>
                <td>${p.cantidad}</td>
                <td>S/ ${p.valor_unitario}</td>
                <td>S/ ${p.descuento}</td>
                <td>S/ ${subtotal.toFixed(2)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="eliminarItem(${index})">X</button>
                </td>
            </tr>
        `;
    });

    document.getElementById('totalGeneral').innerText = "S/ " + total.toFixed(2);
}


/* ELIMINAR */
function eliminarItem(i){
    productosSeleccionados.splice(i,1);
    actualizarTabla();
}


/* BUSCAR CLIENTE */
async function buscarCliente(doc){

    let estado = document.getElementById('clienteEstado');
    estado.innerHTML = 'Buscando...';

    let r = await fetch(`/api/clientes?search=${doc}`);
    let data = await r.json();

    let cliente = data.data.find(c => c.num_doc === doc);

    if(cliente){

        document.getElementById('cliente_razon_social').value = cliente.razon_social || '';
        document.getElementById('cliente_direccion').value = cliente.direccion || '';
        document.getElementById('cliente_email').value = cliente.email || '';
        document.getElementById('cliente_telefono').value = cliente.telefono || '';

        estado.innerHTML = 'Cliente encontrado';

    }else{
        estado.innerHTML = 'Cliente nuevo';
    }
}


/* PROCESAR */
async function procesarFactura(){

    let fecha = document.getElementById('fecha_emision').value.replace('T',' ') + ":00";

    let data = {
        tipo_documento: document.getElementById('tipo_documento').value,
        fecha_emision: fecha,
        moneda: document.getElementById('moneda').value,
        cliente:{
            tipo_doc: document.getElementById('cliente_tipo_doc').value,
            num_doc: document.getElementById('cliente_num_doc').value,
            razon_social: document.getElementById('cliente_razon_social').value
        },
        items: productosSeleccionados
    };

    let r = await fetch('/api/factura/nuevaventa',{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'Accept':'application/json'
        },
        body: JSON.stringify(data)
    });

    let resp = await r.json();

    if(resp.success){
        Swal.fire('OK','Factura generada','success');
        $('#modalFactura').modal('hide');
    }else{
        Swal.fire('Error', resp.message || resp.sunat || 'Error','error');
    }

}

</script>
@endpush