@extends('admin.main')

@section('contenido')

<div class="content">
<div class="container-fluid">
<div class="row">
<div class="col-lg-12">

<div class="card shadow">

<div class="card-header d-flex justify-content-between align-items-center">

    <h5 class="m-0">
        <i class="fas fa-file-invoice"></i> Facturación
    </h5>

    <!-- SOLO ABRE MODAL -->
    <button class="btn btn-primary" data-toggle="modal" data-target="#modalFactura">
        Nueva Factura
    </button>

</div>

<div class="card-body">

    <!-- BUSCADOR -->
    <div class="mb-3">
        <input type="text" id="buscar" class="form-control"
               placeholder="Buscar por cliente, comprobante o documento...">
    </div>

    <div class="table-responsive">

        <table class="table table-hover table-striped table-bordered">

            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Comprobante</th>
                    <th>Cliente</th>
                    <th>Documento</th>
                    <th>Fecha</th>
                    <th>Moneda</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th width="120">Opciones</th>
                </tr>
            </thead>

            <tbody id="facturaTableBody">
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="spinner-border text-primary"></div>
                    </td>
                </tr>
            </tbody>

        </table>

    </div>

    <div id="paginacion" class="mt-3"></div>

</div>
</div>

</div>
</div>
</div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalFactura" tabindex="-1">
<div class="modal-dialog modal-xl">
<div class="modal-content">

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title">Nueva Factura</h5>
    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
</div>

<div class="modal-body">
    @include('factura.registro')
</div>

<div class="modal-footer">
    <!-- SOLO UN BOTÓN -->
    <button class="btn btn-success" id="btnGuardarFactura" onclick="procesarFactura()">
        Registrar Factura
    </button>

    <button class="btn btn-secondary" data-dismiss="modal">
        Cancelar
    </button>
</div>

</div>
</div>
</div>

@endsection
@push('scripts')
<script>

let searchGlobal = '';

/* INIT */
document.addEventListener('DOMContentLoaded', () => {
    cargarFacturas();
});

/* BUSCADOR */
document.getElementById('buscar').addEventListener('keyup', function(){
    searchGlobal = this.value;
    cargarFacturas(1);
});

/* CARGAR FACTURAS */
function cargarFacturas(page = 1) {

    fetch(`/api/facturas?page=${page}&search=${searchGlobal}`)
    .then(res => res.json())
    .then(data => {

        let tbody = document.getElementById('facturaTableBody');
        tbody.innerHTML = '';

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted">
                        No hay resultados
                    </td>
                </tr>`;
            return;
        }

        data.data.forEach(f => {

            let estado = `
                <span class="badge badge-warning">Pendiente</span>
            `;

            if (f.estado_envio === 'aceptado') {
                estado = `<span class="badge badge-success">Aceptado</span>`;
            }
            else if (f.estado_envio === 'rechazado') {
                estado = `<span class="badge badge-danger">Rechazado</span>`;
            }

            tbody.innerHTML += `
                <tr>
                    <td>${f.id}</td>
                    <td><strong>${f.numero_comprobante}</strong></td>
                    <td>${f.nombre_cliente}</td>
                    <td>${f.numero_documento_cliente}</td>
                    <td>${formatearFecha(f.fecha_emision)}</td>
                    <td>${f.moneda}</td>
                    <td>S/ ${parseFloat(f.total_venta).toFixed(2)}</td>
                    <td>${estado}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="verFactura(${f.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="verPdf(${f.id})">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        renderPaginacion(data);

    })
    .catch(err => {
        console.error("Error:", err);
    });

}

/* PAGINACIÓN */
function renderPaginacion(data){

    let html = '';

    for(let i = 1; i <= data.last_page; i++){
        html += `
            <button class="btn btn-sm ${i === data.current_page ? 'btn-primary' : 'btn-light'}"
                    onclick="cargarFacturas(${i})">
                ${i}
            </button>
        `;
    }

    document.getElementById('paginacion').innerHTML = html;
}

/* FECHA */
function formatearFecha(fecha) {
    const f = new Date(fecha);
    return f.toLocaleString('es-PE');
}

/* ACCIONES */
function verFactura(id) {
    window.location.href = "/factura/ver/" + id;
}

function verPdf(id) {
    window.open("/factura/pdf/" + id, "_blank");
}

/* MODAL EVENTOS */
$(document).ready(function(){

    // 🔥 AL ABRIR
    $('#modalFactura').on('shown.bs.modal', function () {
        fetchProductos();
        setFechaActual();
        resetFormularioFactura();
    });

    // 🔥 AL CERRAR → RECARGA TABLA
    $('#modalFactura').on('hidden.bs.modal', function () {
        cargarFacturas();
    });

});

</script>
@endpush
