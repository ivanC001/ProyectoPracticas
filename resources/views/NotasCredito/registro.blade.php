<form id="formNota">
@csrf

<div id="notaValidationBox" class="alert alert-danger d-none mb-3"></div>

<div class="alert alert-info py-2 mb-3">
    Selecciona una factura <strong>aceptada por SUNAT</strong> para generar la nota.
</div>

<div class="form-group">
    <label for="buscarFacturaNota">Buscar factura emitida</label>
    <input type="text" class="form-control" id="buscarFacturaNota" placeholder="Comprobante, cliente o documento...">
</div>

<div class="form-group">
    <label for="venta_id">Factura afectada</label>
    <select class="form-control" id="venta_id"></select>
    <small id="facturaNotaHelp" class="form-text text-muted">Cargando facturas emitidas...</small>
</div>

<div id="facturaSeleccionadaBox" class="border rounded p-2 mb-3 bg-light d-none"></div>

<div class="row">
    <div class="col-md-5">
        <label for="tipo_documento_nota">Tipo de nota</label>
        <select class="form-control" id="tipo_documento_nota">
            <option value="07">Nota de Credito</option>
            <option value="08">Nota de Debito</option>
        </select>
    </div>
    <div class="col-md-7">
        <label for="codMotivo">Motivo SUNAT</label>
        <select class="form-control" id="codMotivo"></select>
    </div>
</div>

<div class="form-group mt-3 mb-0">
    <label for="desMotivo">Descripcion del motivo</label>
    <input type="text" class="form-control" id="desMotivo" maxlength="255" placeholder="Describe brevemente el motivo de la nota">
</div>

</form>

@push('scripts')
<script>

const MOTIVOS_NOTA = {
    '07': [
        { code: '01', label: 'Anulacion de la operacion' },
        { code: '02', label: 'Anulacion por error en RUC' },
        { code: '03', label: 'Correccion por error en descripcion' },
        { code: '04', label: 'Descuento global' },
        { code: '05', label: 'Descuento por item' },
        { code: '06', label: 'Devolucion total' },
        { code: '07', label: 'Devolucion por item' },
        { code: '08', label: 'Bonificacion' },
        { code: '09', label: 'Disminucion en el valor' },
        { code: '10', label: 'Otros conceptos' },
        { code: '11', label: 'Ajustes de operaciones de exportacion' },
        { code: '12', label: 'Ajustes por montos y/o fechas de pago' },
        { code: '13', label: 'Correccion del monto neto pendiente de pago' },
    ],
    '08': [
        { code: '01', label: 'Intereses por mora' },
        { code: '02', label: 'Aumento en el valor' },
        { code: '03', label: 'Penalidades y/o otros conceptos' },
    ],
};

let facturasEmitidasNota = [];
let timeoutBuscarFacturaNota = null;

document.addEventListener('DOMContentLoaded', function() {
    actualizarMotivosNota();
    cargarFacturasEmitidasNota();
});

document.getElementById('tipo_documento_nota').addEventListener('change', function() {
    actualizarMotivosNota();
    limpiarErroresNota();
});

document.getElementById('codMotivo').addEventListener('change', limpiarErroresNota);
document.getElementById('desMotivo').addEventListener('input', limpiarErroresNota);
document.getElementById('venta_id').addEventListener('change', function() {
    renderFacturaSeleccionadaNota();
    limpiarErroresNota();
});

document.getElementById('buscarFacturaNota').addEventListener('keyup', function() {
    clearTimeout(timeoutBuscarFacturaNota);
    const term = this.value.trim();
    timeoutBuscarFacturaNota = setTimeout(() => {
        cargarFacturasEmitidasNota(term);
    }, 350);
});

function actualizarMotivosNota() {
    const tipoNota = document.getElementById('tipo_documento_nota').value;
    const select = document.getElementById('codMotivo');
    const motivos = MOTIVOS_NOTA[tipoNota] || [];

    select.innerHTML = '';
    motivos.forEach((motivo) => {
        const option = document.createElement('option');
        option.value = motivo.code;
        option.textContent = `${motivo.code} - ${motivo.label}`;
        select.appendChild(option);
    });
}

function renderFacturaOptionsNota() {
    const select = document.getElementById('venta_id');
    const help = document.getElementById('facturaNotaHelp');
    select.innerHTML = '';

    if (!facturasEmitidasNota.length) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No se encontraron facturas aceptadas';
        select.appendChild(option);
        help.textContent = 'No hay resultados para la busqueda actual.';
        renderFacturaSeleccionadaNota();
        return;
    }

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Seleccione una factura...';
    select.appendChild(placeholder);

    facturasEmitidasNota.forEach((f) => {
        const option = document.createElement('option');
        option.value = f.id;
        const simbolo = f.moneda === 'USD' ? 'US$' : 'S/';
        option.textContent = `${f.numero_comprobante} | ${f.nombre_cliente} | ${simbolo} ${Number(f.total_venta).toFixed(2)}`;
        select.appendChild(option);
    });

    help.textContent = `Mostrando ${facturasEmitidasNota.length} facturas aceptadas.`;
}

function renderFacturaSeleccionadaNota() {
    const selectedId = Number(document.getElementById('venta_id').value || 0);
    const box = document.getElementById('facturaSeleccionadaBox');
    const factura = facturasEmitidasNota.find((f) => Number(f.id) === selectedId);

    if (!factura) {
        box.classList.add('d-none');
        box.innerHTML = '';
        return;
    }

    const simbolo = factura.moneda === 'USD' ? 'US$' : 'S/';
    box.classList.remove('d-none');
    box.innerHTML = `
        <div><strong>Factura:</strong> ${factura.numero_comprobante}</div>
        <div><strong>Cliente:</strong> ${factura.nombre_cliente}</div>
        <div><strong>Doc cliente:</strong> ${factura.numero_documento_cliente || '-'}</div>
        <div><strong>Total:</strong> ${simbolo} ${Number(factura.total_venta).toFixed(2)}</div>
    `;
}

async function cargarFacturasEmitidasNota(search = '') {
    const help = document.getElementById('facturaNotaHelp');
    help.textContent = 'Buscando facturas emitidas...';

    try {
        const q = search ? `?search=${encodeURIComponent(search)}` : '';
        const data = await apiFetch(`/api/facturacion/facturas-emitidas${q}`);
        facturasEmitidasNota = data.data || [];
        renderFacturaOptionsNota();
    } catch (error) {
        facturasEmitidasNota = [];
        renderFacturaOptionsNota();
        help.textContent = 'No se pudo cargar facturas emitidas.';
    }
}

function limpiarErroresNota() {
    const box = document.getElementById('notaValidationBox');
    box.classList.add('d-none');
    box.innerHTML = '';

    ['venta_id', 'tipo_documento_nota', 'codMotivo', 'desMotivo'].forEach((id) => {
        const input = document.getElementById(id);
        if (input) {
            input.classList.remove('is-invalid');
        }
    });
}

function mostrarErroresNota(err) {
    limpiarErroresNota();

    const box = document.getElementById('notaValidationBox');
    const map = {
        venta_id: 'venta_id',
        tipo_documento: 'tipo_documento_nota',
        codMotivo: 'codMotivo',
        desMotivo: 'desMotivo',
    };

    if (!err || !err.errors) {
        Swal.fire('Error', err?.message || 'No se pudo registrar la nota', 'error');
        return;
    }

    const messages = [];
    Object.keys(err.errors).forEach((key) => {
        (err.errors[key] || []).forEach((msg) => messages.push(msg));

        const fieldId = map[key];
        if (fieldId) {
            const input = document.getElementById(fieldId);
            if (input) {
                input.classList.add('is-invalid');
            }
        }
    });

    box.innerHTML = `
        <strong>Corrige lo siguiente:</strong>
        <ul class="mb-0 mt-2">${messages.map((msg) => `<li>${msg}</li>`).join('')}</ul>
    `;
    box.classList.remove('d-none');
}

async function procesarNota() {
    limpiarErroresNota();

    const payload = {
        venta_id: document.getElementById('venta_id').value,
        tipo_documento: document.getElementById('tipo_documento_nota').value,
        codMotivo: document.getElementById('codMotivo').value,
        desMotivo: document.getElementById('desMotivo').value.trim(),
    };

    try {
        await apiFetch('/api/facturacion/notas', {
            method: 'POST',
            body: JSON.stringify(payload),
        });

        Swal.fire('OK', 'Nota enviada a procesamiento', 'success');
        $('#modalNota').modal('hide');
        cargarNotas();
    } catch (err) {
        mostrarErroresNota(err);
    }
}

</script>
@endpush
