let productosSeleccionados = [];

// Cargar productos desde la API
function fetchProductos() {
    fetch('/api/productos')
        .then(response => response.json())
        .then(data => {
            const productosSelect = document.getElementById('productos');
            productosSelect.innerHTML = "<option value=''>Seleccione un producto</option>";
            data.forEach(producto => {
                const option = document.createElement('option');
                option.value = producto.id;
                option.text = producto.descripcion;
                option.setAttribute('data-precio', producto.precio);
                option.setAttribute('data-codigo', producto.codigo);
                productosSelect.appendChild(option);
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al cargar los productos.',
            });
            console.error('Error al cargar productos:', error);
        });
}

// Manejar el cambio de producto seleccionado
document.getElementById('productos').addEventListener('change', function () {
    const productoPrecio = this.options[this.selectedIndex]?.getAttribute('data-precio') || 0;
    const cantidadInput = document.getElementById('cantidadProducto');
    const precioTotalInput = document.getElementById('precioTotalProducto');

    if (!this.value) {
        cantidadInput.value = '';
        precioTotalInput.value = 'S/ 0.00';
        return;
    }

    cantidadInput.value = 1; // Predeterminado a 1
    precioTotalInput.value = `S/ ${(parseFloat(productoPrecio) * 1).toFixed(2)}`; // Total inicial
});

// Manejar el cambio de cantidad
document.getElementById('cantidadProducto').addEventListener('input', function () {
    const cantidad = parseInt(this.value) || 0;
    const productoSelect = document.getElementById('productos');
    const productoPrecio = productoSelect.options[productoSelect.selectedIndex]?.getAttribute('data-precio') || 0;
    const precioTotalInput = document.getElementById('precioTotalProducto');

    if (cantidad > 0) {
        const total = cantidad * parseFloat(productoPrecio);
        precioTotalInput.value = `S/ ${total.toFixed(2)}`;
    } else {
        precioTotalInput.value = 'S/ 0.00';
    }
});

// Agregar producto a la lista
function agregarProducto() {
    const select = document.getElementById('productos');
    const productoCodigo = select.options[select.selectedIndex]?.getAttribute('data-codigo');
    const productoDescripcion = select.options[select.selectedIndex]?.text;
    const productoPrecio = select.options[select.selectedIndex]?.getAttribute('data-precio');
    const cantidad = parseInt(document.getElementById('cantidadProducto').value);

    if (!productoCodigo || cantidad <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Seleccione un producto y una cantidad válida.',
        });
        return;
    }

    productosSeleccionados.push({
        unidad: 'NIU',
        cantidad: cantidad,
        codigo: productoCodigo,
        descripcion: productoDescripcion,
        valor_unitario: parseFloat(productoPrecio),
    });

    actualizarListaProductos();
    limpiarSeleccion();
}

// Actualizar la lista de productos seleccionados
function actualizarListaProductos() {
    const lista = document.getElementById('listaProductosSeleccionados');
    const totalGeneralElement = document.getElementById('totalGeneral');
    lista.innerHTML = '';

    let precioTotal = 0;

    productosSeleccionados.forEach((producto, index) => {
        const subtotal = producto.valor_unitario * producto.cantidad;
        precioTotal += subtotal;

        const item = document.createElement('li');
        item.className = 'list-group-item d-flex justify-content-between align-items-center';
        item.innerHTML = `
            ${producto.descripcion} - S/ ${producto.valor_unitario.toFixed(2)} x ${producto.cantidad} unidad(es) = S/ ${subtotal.toFixed(2)}
            <span>
                <button type="button" class="btn btn-sm btn-warning" onclick="incrementarCantidad(${index})">+</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${index})">Eliminar</button>
            </span>
        `;
        lista.appendChild(item);
    });

    totalGeneralElement.textContent = `S/ ${precioTotal.toFixed(2)}`;
}

// Incrementar la cantidad de un producto
function incrementarCantidad(index) {
    productosSeleccionados[index].cantidad++;
    actualizarListaProductos();
}

// Eliminar un producto de la lista
function eliminarProducto(index) {
    productosSeleccionados.splice(index, 1);
    actualizarListaProductos();
}

// Limpiar selección de producto
function limpiarSeleccion() {
    document.getElementById('productos').value = '';
    document.getElementById('cantidadProducto').value = '';
    document.getElementById('precioTotalProducto').value = 'S/ 0.00';
}

// Manejar el envío del formulario
document.getElementById('formRegistroFactura').addEventListener('submit', function (event) {
    event.preventDefault();

    const tipoDoc = document.getElementById('cliente_tipo_doc').value;
    const numDoc = document.getElementById('cliente_num_doc').value || '-';
    const razonSocial = document.getElementById('cliente_razon_social').value || 'Sin Razón Social';
    const moneda = document.getElementById('moneda').value;

    const data = {
        tipo_comprobante: '01',
        cliente: {
            tipo_doc: tipoDoc,
            num_doc: numDoc,
            razon_social: razonSocial,
        },
        factura: {
            moneda: moneda,
        },
        detalle: productosSeleccionados,
    };

    fetch('/api/comprobantes/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
        },
        body: JSON.stringify(data),
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la API');
            }
            return response.json();
        })
        .then(result => {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Factura registrada con éxito.',
            });
            console.log(result);
            productosSeleccionados = [];
            actualizarListaProductos();
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al registrar la factura.',
            });
            console.error('Error al registrar factura:', error);
        });
});

// Inicializar la carga de productos
document.addEventListener('DOMContentLoaded', fetchProductos);
