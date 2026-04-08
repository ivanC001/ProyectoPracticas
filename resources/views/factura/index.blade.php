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

<div class="modal fade" id="modalPdfFactura" tabindex="-1">
<div class="modal-dialog modal-xl modal-dialog-centered">
<div class="modal-content">

<div class="modal-header bg-danger text-white">
    <h5 class="modal-title">
        <i class="fas fa-file-pdf"></i> Vista previa de factura
    </h5>
    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
</div>

<div class="modal-body p-0" style="height:80vh;">
    <iframe id="pdfFacturaFrame" src="about:blank" style="width:100%;height:100%;border:0;"></iframe>
</div>

<div class="modal-footer justify-content-between">
    <small class="text-muted" id="pdfFacturaHint">Selecciona una factura para visualizar su PDF.</small>
    <div>
        <a id="btnDescargarPdfFactura" class="btn btn-danger" href="#" target="_blank" rel="noopener">
            <i class="fas fa-download"></i> Descargar PDF
        </a>
        <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
let facturaPdfActual = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarFacturas();
});

/* BUSCADOR */
document.getElementById('buscar').addEventListener('keyup', function(){
    searchGlobal = this.value;
    cargarFacturas(1);
});

/* CARGAR */
function cargarFacturas(page = 1) {

    apiFetch(`/api/facturas?page=${page}&search=${searchGlobal}`)
    .then(data => {

        let tbody = document.getElementById('facturaTableBody');
        tbody.innerHTML = '';

        let lista = data.data || [];

        if (lista.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted">
                        No hay resultados
                    </td>
                </tr>`;
            return;
        }

        lista.forEach(f => {

            let estado = `<span class="badge badge-warning">Pendiente</span>`;

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
                        <button class="btn btn-sm btn-info" onclick="mostrarPdf(${f.id}, '${(f.numero_comprobante || '').replace(/'/g, "\\'")}')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="descargarPdf(${f.id})">
                            <i class="fas fa-download"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        renderPaginacion(data);

    })
    .catch(err => {
        console.error("ERROR FACTURAS:", err);
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
    return new Date(fecha).toLocaleString('es-PE');
}

/* ACCIONES */
function getPdfUrl(id) {
    return `/factura/pdf/${id}`;
}

function getPdfDownloadUrl(id) {
    return `/factura/pdf/${id}/descargar`;
}

function mostrarPdf(id, numeroComprobante = '') {
    facturaPdfActual = id;

    document.getElementById('pdfFacturaFrame').src = getPdfUrl(id);
    document.getElementById('btnDescargarPdfFactura').href = getPdfDownloadUrl(id);
    document.getElementById('pdfFacturaHint').innerText = numeroComprobante
        ? `Factura ${numeroComprobante}`
        : `Factura #${id}`;

    $('#modalPdfFactura').modal('show');
}

function descargarPdf(id) {
    window.open(getPdfDownloadUrl(id), '_blank');
}

/* MODAL */
$(document).ready(function(){

    $('#modalFactura').on('shown.bs.modal', function () {
        fetchProductos();
        setFechaActual();
    });

    $('#modalFactura').on('hidden.bs.modal', function () {
        cargarFacturas();
    });

    $('#modalPdfFactura').on('hidden.bs.modal', function () {
        document.getElementById('pdfFacturaFrame').src = 'about:blank';
        document.getElementById('btnDescargarPdfFactura').href = '#';
        document.getElementById('pdfFacturaHint').innerText = 'Selecciona una factura para visualizar su PDF.';
        facturaPdfActual = null;
    });

});

</script>
@endpush
