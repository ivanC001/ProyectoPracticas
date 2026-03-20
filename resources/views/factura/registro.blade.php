<form id="formRegistroFactura">

@csrf

<div class="form-group">
    <label>Tipo Comprobante</label>
    <select class="form-control" id="tipo_documento">
        <option value="01">Factura</option>
        <option value="03">Boleta</option>
    </select>
</div>

<div class="form-group">
    <label>Fecha Emisión</label>
    <input type="datetime-local" class="form-control" id="fecha_emision">
</div>

<div class="form-row">

    <div class="form-group col-md-4">
        <label>Tipo Doc</label>
        <select class="form-control" id="cliente_tipo_doc">
            <option value="1">DNI</option>
            <option value="6">RUC</option>
        </select>
    </div>

    <div class="form-group col-md-4">
        <label>Número</label>
        <input type="text" class="form-control" id="cliente_num_doc">
    </div>

    <div class="form-group col-md-4">
        <label>Razón Social</label>
        <input type="text" class="form-control" id="cliente_razon_social">
    </div>

</div>

<div class="form-group">
    <label>Moneda</label>
    <select class="form-control" id="moneda">
        <option value="PEN">Soles</option>
        <option value="USD">Dólares</option>
    </select>
</div>

<div class="form-group">
    <label>Producto</label>
    <select class="form-control" id="productos"></select>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Cantidad</label>
        <input type="number" class="form-control" id="cantidadProducto">
    </div>

    <div class="form-group col-md-6">
        <label>Total</label>
        <input type="text" class="form-control" id="precioTotalProducto" readonly>
    </div>
</div>

<button type="button" class="btn btn-secondary" onclick="agregarProducto()">Agregar</button>

<ul class="list-group mt-3" id="listaProductosSeleccionados"></ul>

<h4>Total: <span id="totalGeneral">S/ 0.00</span></h4>

</form>


@push('scripts')
<script>

let productosSeleccionados = [];

/* FECHA ACTUAL */
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

        console.log("PRODUCTOS:", data);

        let select = document.getElementById('productos');

        if(!select){
            console.error("NO EXISTE SELECT PRODUCTOS");
            return;
        }

        select.innerHTML = "<option value=''>Seleccione</option>";

        // 🔥 AQUÍ ESTÁ LA CORRECCIÓN
        data.data.forEach(p=>{

            let opt = document.createElement('option');

            opt.value = p.codigo;
            opt.text = `${p.descripcion} (Stock: ${p.stock})`;

            opt.setAttribute('data-precio', p.precio);
            opt.setAttribute('data-codigo', p.codigo);
            opt.setAttribute('data-stock', p.stock);

            if(p.stock <= 0){
                opt.disabled = true;
            }

            select.appendChild(opt);

        });

    })
    .catch(err=>{
        console.error("ERROR FETCH:", err);
    });
}
/* CAMBIO TIPO DOCUMENTO */
document.getElementById('tipo_documento').addEventListener('change', function(){

    let tipo = this.value;

    if(tipo === '03'){ // BOLETA
        document.getElementById('cliente_tipo_doc').value = "1";
        document.getElementById('cliente_tipo_doc').disabled = true;

        document.getElementById('cliente_num_doc').required = false;
        document.getElementById('cliente_razon_social').required = false;
    }
    else{ // FACTURA
        document.getElementById('cliente_tipo_doc').value = "6";
        document.getElementById('cliente_tipo_doc').disabled = true;

        document.getElementById('cliente_num_doc').required = true;
        document.getElementById('cliente_razon_social').required = true;
    }

});

/* AGREGAR PRODUCTO */
function agregarProducto(){

    let select = document.getElementById('productos');

    if(!select.value){
        Swal.fire('Error','Seleccione un producto','error');
        return;
    }

    let cantidad = parseFloat(document.getElementById('cantidadProducto').value);

    if(!cantidad || cantidad <= 0){
        Swal.fire('Error','Cantidad inválida','error');
        return;
    }

    let codigo = select.options[select.selectedIndex].getAttribute('data-codigo');

    // 🔥 EVITAR DUPLICADOS
    let existente = productosSeleccionados.find(p => p.codigo === codigo);

    if(existente){
        existente.cantidad += cantidad;
    }else{
        let item = {
            codigo: codigo,
            descripcion: select.options[select.selectedIndex].text,
            unidad: "NIU",
            cantidad: cantidad,
            valor_unitario: parseFloat(select.options[select.selectedIndex].getAttribute('data-precio'))
        };

        productosSeleccionados.push(item);
    }

    actualizarLista();
}

/* LISTA */
function actualizarLista(){

    let lista = document.getElementById('listaProductosSeleccionados');
    let total = 0;

    lista.innerHTML = '';

    productosSeleccionados.forEach((p,index)=>{

        let sub = p.cantidad * p.valor_unitario;
        total += sub;

        lista.innerHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                ${p.descripcion} | Cant: ${p.cantidad} | S/ ${sub.toFixed(2)}
                <button class="btn btn-sm btn-danger" onclick="eliminarItem(${index})">X</button>
            </li>
        `;
    });

    document.getElementById('totalGeneral').innerText = "S/ " + total.toFixed(2);
}

/* ELIMINAR */
function eliminarItem(index){
    productosSeleccionados.splice(index,1);
    actualizarLista();
}

/* VALIDAR CLIENTE */
function validarCliente(){

    let tipo = document.getElementById('tipo_documento').value;

    if(tipo === '01'){ // FACTURA

        let ruc = document.getElementById('cliente_num_doc').value;
        let razon = document.getElementById('cliente_razon_social').value;

        if(!ruc || ruc.length !== 11){
            Swal.fire('Error','RUC inválido','error');
            return false;
        }

        if(!razon){
            Swal.fire('Error','Ingrese razón social','error');
            return false;
        }
    }

    return true;
}

/* ENVIAR */
function procesarFactura(){

    if(productosSeleccionados.length === 0){
        Swal.fire('Error','Agrega productos','error');
        return;
    }

    if(!validarCliente()) return;

    let fecha = document.getElementById('fecha_emision').value;

    if(!fecha){
        Swal.fire('Error','Ingrese fecha','error');
        return;
    }

    fecha = fecha.replace('T',' ') + ":00";

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

    Swal.fire({
        title: 'Confirmar envío',
        html: `<pre style="text-align:left">${JSON.stringify(data,null,2)}</pre>`,
        width:700,
        showCancelButton:true,
        confirmButtonText:'Enviar'
    }).then(result => {

        if(result.isConfirmed){

            fetch('/api/factura/nuevaventa',{
                method:'POST',
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(r=>r.json())
            .then(resp=>{

                mostrarRespuesta(resp);

                if(resp.success){
                    $('#modalFactura').modal('hide');
                    productosSeleccionados = [];
                    actualizarLista();
                }

            })
            .catch(()=>{
                Swal.fire('Error','No se pudo enviar','error');
            });

        }

    });
}


</script>
@endpush