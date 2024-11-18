let productosSeleccionados = [];

// Cargar productos desde la API y llenar el select
function fetchProductos() {
    fetch('/api/productos')
        .then(response => {
            if (!response.ok) {
                throw new Error("Error al cargar los productos");
            }
            return response.json();
        })
        .then(data => {
            const productosSelect = document.getElementById('productos');
            productosSelect.innerHTML = "<option value=''>Seleccione un producto</option>";

            data.forEach(producto => {
                const option = document.createElement('option');
                option.value = producto.id;
                option.text = `${producto.nombre} - ${producto.descripcion}`;
                option.setAttribute('data-precio', producto.precio);
                productosSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error al cargar productos:', error));
}

// Manejar el cambio de producto seleccionado
document.getElementById('productos').addEventListener('change', function () {
    const productoId = this.value;
    const productoPrecio = this.options[this.selectedIndex]?.getAttribute('data-precio') || 0;
    const cantidadInput = document.getElementById('cantidadProducto');
    const precioTotalInput = document.getElementById('precioTotalProducto');

    if (!productoId) {
        cantidadInput.value = '';
        precioTotalInput.value = 'S/ 0.00';
        return;
    }

    cantidadInput.value = 1; // Predeterminado a 1 unidad
    precioTotalInput.value = `S/ ${parseFloat(productoPrecio).toFixed(2)}`;
});

// Manejar el cambio en la cantidad
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

// Agregar un producto a la lista
function agregarProducto() {
    const select = document.getElementById('productos');
    const productoId = select.value;
    const productoNombre = select.options[select.selectedIndex]?.text;
    const productoPrecio = select.options[select.selectedIndex]?.getAttribute('data-precio');
    const cantidad = parseInt(document.getElementById('cantidadProducto').value) || 0;

    if (!productoId || cantidad <= 0) {
        alert("Por favor, selecciona un producto y una cantidad válida.");
        return;
    }

    if (productosSeleccionados.some(producto => producto.id === productoId)) {
        alert("Este producto ya está en la lista.");
        return;
    }

    productosSeleccionados.push({
        id: productoId,
        nombre: productoNombre,
        precio: parseFloat(productoPrecio),
        cantidad: cantidad,
    });

    actualizarListaProductos();
    limpiarSeleccion();
}

// Actualizar la lista de productos seleccionados y el precio total
function actualizarListaProductos() {
    const lista = document.getElementById('listaProductosSeleccionados');
    const totalGeneralElement = document.getElementById('totalGeneral');
    lista.innerHTML = '';

    let precioTotal = 0;

    productosSeleccionados.forEach((producto, index) => {
        const item = document.createElement('li');
        item.className = 'list-group-item d-flex justify-content-between align-items-center';

        const subtotal = producto.precio * producto.cantidad;
        precioTotal += subtotal;

        item.innerHTML = `
            ${producto.nombre} - S/ ${producto.precio.toFixed(2)} x ${producto.cantidad} unidad(es) = S/ ${subtotal.toFixed(2)}
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

// Limpiar la selección de producto y los campos relacionados
function limpiarSeleccion() {
    document.getElementById('productos').value = '';
    document.getElementById('cantidadProducto').value = '';
    document.getElementById('precioTotalProducto').value = 'S/ 0.00';
}

// Inicializar la carga de productos al cargar la página
document.addEventListener('DOMContentLoaded', fetchProductos);
