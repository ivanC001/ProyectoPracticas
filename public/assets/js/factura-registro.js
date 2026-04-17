(function () {
  const CFG = window.__FACTURA_REGISTRO_CONFIG__ || {};
  const IGV_CATALOG = CFG.igvCatalog || {};
  const DETRACCION_CATALOG = CFG.detraccionCatalog || {};
  const DETRACCION_MEDIO_PAGO = CFG.detraccionMedioPago || '001';
  const DETRACCION_MINIMO_SERVICIOS = Number(CFG.detraccionMinimoServicios || 700);
  const OBS_SPOT_PREFIX = 'OPERACION SUJETA AL SPOT';

  const IGV_GROUP_LABELS = {
    gravada: 'Gravada',
    exonerada: 'Exonerada',
    inafecta: 'Inafecta',
    gratuita: 'Gratuita',
    exportacion: 'Exportacion',
  };

  let itemsSeleccionados = [];
  let productosDisponibles = [];
  let serviciosDisponibles = [];
  let timeoutCliente = null;
  let detraccionManual = false;

  function el(id) { return document.getElementById(id); }
  function money() { return el('moneda').value === 'USD' ? 'US$' : 'S/'; }
  function tipMeta(code) { return IGV_CATALOG[String(code)] || IGV_CATALOG['10']; }

  function toLocalInput(date) {
    const offset = date.getTimezoneOffset();
    return new Date(date.getTime() - offset * 60000).toISOString().slice(0, 16);
  }

  function fromServerDateTime(value) {
    if (!value) return toLocalInput(new Date());
    const normalized = String(value).replace(' ', 'T');
    const dt = new Date(normalized);
    if (Number.isNaN(dt.getTime())) return toLocalInput(new Date());
    return toLocalInput(dt);
  }

  window.setFechaActual = function setFechaActual() {
    const input = el('fecha_emision');
    if (!input) return;
    const now = new Date();
    const max = toLocalInput(now);
    const min = toLocalInput(new Date(now.getTime() - (2 * 24 * 60 * 60 * 1000)));
    input.max = max;
    input.min = min;
    if (!input.value) input.value = max;
    const fec = input.value.slice(0, 10);
    el('credito_fecha_vencimiento').min = fec;
    if (!el('credito_fecha_vencimiento').value) el('credito_fecha_vencimiento').value = fec;
  };

  function line(item) {
    const qty = Math.max(Number(item.cantidad || 0), 0);
    const vu = Math.max(Number(item.valor_unitario || 0), 0);
    const ds = Math.max(Number(item.descuento || 0), 0);
    const base = Math.max((qty * vu) - ds, 0);
    const meta = tipMeta(item.tip_afe_igv);
    const group = meta.group || 'gravada';
    const igv = group === 'gravada' ? +(base * 0.18).toFixed(2) : 0;
    const subtotal = group === 'gratuita' ? 0 : +base.toFixed(2);
    const total = group === 'gratuita' ? 0 : +(subtotal + igv).toFixed(2);
    return { group, subtotal, total, igv, base, aplicaIgv: group === 'gravada' };
  }

  function resumen() {
    const out = { gravada: 0, exonerada: 0, inafecta: 0, exportacion: 0, gratuita: 0, igv: 0, total: 0 };
    itemsSeleccionados.forEach(item => {
      const l = line(item);
      out[l.group] += l.base;
      out.igv += l.igv;
      out.total += l.total;
    });
    return out;
  }

  function badge(group) {
    return `<span class="chip">${IGV_GROUP_LABELS[group] || 'Gravada'}</span>`;
  }

  function renderAfectacionInfo() {
    const code = el('tipoAfectacionIgv').value;
    const meta = tipMeta(code);
    const legend = meta.pdf_legend ? `Leyenda: ${meta.pdf_legend}` : 'Sin leyenda adicional.';
    el('tipoAfectacionInfo').innerHTML = `${badge(meta.group)} <strong>${code}</strong> - ${meta.label}. ${legend}`;
  }

  function boletaSinDniPermitida() {
    return el('tipo_documento').value === '03' && el('moneda').value === 'PEN' && resumen().total <= 500;
  }

  function totalServicios() {
    return itemsSeleccionados.reduce((sum, item) => {
      if (item.tipo_item !== 'servicio') return sum;
      return sum + line(item).total;
    }, 0);
  }

  function requiereDetraccionServicios() {
    return totalServicios() > DETRACCION_MINIMO_SERVICIOS;
  }

  function renderReglasOperacion() {
    const box = el('reglasOperacionHint');
    if (!box) return;

    const esFactura = el('tipo_documento').value === '01';
    const total = resumen().total;
    const totalServ = totalServicios();
    const conServicios = tieneServicios();

    if (!esFactura) {
      if (total > 500) {
        box.innerHTML = `<strong>Regla boleta:</strong> total ${money()} ${total.toFixed(2)}. Debe usar DNI del cliente (8 digitos).`;
      } else {
        box.innerHTML = `<strong>Regla boleta:</strong> total ${money()} ${total.toFixed(2)}. Puede emitir con DNI, RUC o sin documento.`;
      }
      return;
    }

    if (!conServicios) {
      box.innerHTML = '<strong>Regla factura:</strong> factura sin servicios. No aplica detraccion.';
      return;
    }

    if (requiereDetraccionServicios()) {
      box.innerHTML = `<strong>Regla factura con servicios:</strong> servicios ${money()} ${totalServ.toFixed(2)} > ${money()} ${DETRACCION_MINIMO_SERVICIOS.toFixed(2)}. Detraccion obligatoria.`;
      return;
    }

    box.innerHTML = `<strong>Regla factura con servicios:</strong> servicios ${money()} ${totalServ.toFixed(2)} <= ${money()} ${DETRACCION_MINIMO_SERVICIOS.toFixed(2)}. Detraccion no obligatoria.`;
  }

  function validarReglaBoletaDocumento() {
    if (el('tipo_documento').value !== '03') return true;
    const tipoDoc = el('cliente_tipo_doc').value;
    const doc = el('cliente_num_doc').value.trim();
    const estado = el('clienteEstado');
    if (boletaSinDniPermitida()) {
      if (tipoDoc === '6' && doc !== '' && !/^\d{11}$/.test(doc)) {
        estado.className = 'form-text text-danger';
        estado.innerHTML = 'Si ingresas RUC en boleta, debe tener 11 digitos.';
        return false;
      }

      if (tipoDoc === '1' && doc !== '' && !/^\d{8}$/.test(doc)) {
        estado.className = 'form-text text-danger';
        estado.innerHTML = 'Si ingresas DNI en boleta, debe tener 8 digitos.';
        return false;
      }
      return true;
    }
    if (tipoDoc !== '1' || !/^\d{8}$/.test(doc)) {
      estado.className = 'form-text text-danger';
      estado.innerHTML = 'Boleta > S/ 500 requiere DNI valido.';
      return false;
    }
    return true;
  }

  function aplicarReglasComprobante() {
    const factura = el('tipo_documento').value === '01';
    const sel = el('cliente_tipo_doc');
    const sinDoc = sel.querySelector('option[value="0"]');
    const dni = sel.querySelector('option[value="1"]');
    const ruc = sel.querySelector('option[value="6"]');
    if (factura) {
      sel.value = '6';
      sel.disabled = true;
      if (sinDoc) sinDoc.disabled = true;
      if (dni) dni.disabled = true;
      if (ruc) ruc.disabled = false;
      el('tipoComprobanteHelper').innerText = 'Factura: cliente con RUC obligatorio.';
      el('cliente_num_doc').placeholder = 'RUC (11 digitos)';
    } else {
      sel.disabled = false;
      if (sinDoc) sinDoc.disabled = false;
      if (dni) dni.disabled = false;
      if (ruc) ruc.disabled = false;
      if (!['0', '1', '6'].includes(sel.value)) sel.value = '1';
      el('tipoComprobanteHelper').innerText = 'Boleta: puede ser con DNI o RUC; si supera S/ 500, exige DNI.';
    }
    validarReglaBoletaDocumento();
    actualizarDetraccionPanel();
    renderReglasOperacion();
    recalcDetraccion();
  }

  function aplicarReglasFormaPago() {
    el('creditoPanel').classList.toggle('d-none', el('forma_pago').value !== 'credito');
  }

  function tieneServicios() {
    return itemsSeleccionados.some(i => i.tipo_item === 'servicio');
  }

  function actualizarDetraccionPanel() {
    const show = el('tipo_documento').value === '01' && tieneServicios();
    const requiere = requiereDetraccionServicios();
    el('detraccionPanel').classList.toggle('d-none', !show);

    const sw = el('detraccion_aplica');
    const code = el('detraccion_codigo');
    const cuenta = el('detraccion_cuenta');
    const base = el('detraccion_base');
    const monto = el('detraccion_monto');
    const hint = el('detraccionHint');

    if (!show) {
      sw.checked = false;
      sw.disabled = true;
      detraccionManual = false;
      if (hint) hint.innerHTML = '';
    } else if (requiere) {
      sw.checked = true;
      sw.disabled = true;
      if (hint) {
        hint.className = 'alert alert-warning py-2 mb-2';
        hint.innerHTML = `Servicio mayor a ${money()} ${DETRACCION_MINIMO_SERVICIOS.toFixed(2)}. Detraccion obligatoria.`;
      }
    } else {
      sw.checked = false;
      sw.disabled = true;
      detraccionManual = false;
      if (hint) {
        hint.className = 'alert alert-secondary py-2 mb-2';
        hint.innerHTML = `Servicio menor o igual a ${money()} ${DETRACCION_MINIMO_SERVICIOS.toFixed(2)}. Detraccion no aplica.`;
      }
    }

    const puedeEditar = show && requiere;
    code.disabled = !puedeEditar;
    cuenta.disabled = !puedeEditar;
    base.disabled = !puedeEditar;
    monto.disabled = !puedeEditar;

    if (!puedeEditar) {
      base.value = '0.00';
      monto.value = '0.00';
    }

    const obs = (el('observacion').value || '').trim().toUpperCase();
    if ((!show || !requiere) && obs.startsWith(OBS_SPOT_PREFIX)) {
      el('observacion').value = '';
    }
  }

  function syncDetraccionPct() {
    const code = el('detraccion_codigo').value;
    const pct = Number((DETRACCION_CATALOG[code] || {}).porcentaje || 0);
    el('detraccion_porcentaje').value = pct.toFixed(2);
  }

  function recalcDetraccion() {
    const aplica = el('detraccion_aplica').checked;
    const pct = Number(el('detraccion_porcentaje').value || 0);
    const baseServicios = totalServicios();
    const montoSugerido = aplica ? +(baseServicios * (pct / 100)).toFixed(2) : 0;
    el('detraccion_base').value = baseServicios.toFixed(2);

    const campoMonto = el('detraccion_monto');
    if (!aplica) {
      detraccionManual = false;
      campoMonto.value = '0.00';
    } else {
      const montoActual = Number(campoMonto.value || 0);
      const montoActualValido = Number.isFinite(montoActual) && montoActual >= 0;
      if (!detraccionManual || !montoActualValido) {
        campoMonto.value = montoSugerido.toFixed(2);
      }
    }

    const aplicaSpot = el('tipo_documento').value === '01'
      && tieneServicios()
      && requiereDetraccionServicios()
      && aplica;

    const obsActual = (el('observacion').value || '').trim();
    if (aplicaSpot && !obsActual) {
      el('observacion').value = `${OBS_SPOT_PREFIX} ${pct.toFixed(2)}%`;
      return;
    }

    if (!aplicaSpot && obsActual.toUpperCase().startsWith(OBS_SPOT_PREFIX)) {
      el('observacion').value = '';
    }
  }

  function updateTabla() {
    const body = el('tablaItems');
    body.innerHTML = '';
    const sym = money();
    itemsSeleccionados.forEach((item, idx) => {
      const l = line(item);
      const meta = tipMeta(item.tip_afe_igv);
      body.innerHTML += `<tr>
        <td>${item.tipo_item === 'servicio' ? 'Servicio' : 'Producto'}</td>
        <td>${item.descripcion}</td>
        <td>${badge(meta.group)} <span class="small text-muted">${item.tip_afe_igv}</span></td>
        <td>${item.cantidad}</td>
        <td>${sym} ${Number(item.valor_unitario).toFixed(2)}</td>
        <td>${sym} ${Number(item.descuento).toFixed(2)}</td>
        <td>${sym} ${l.subtotal.toFixed(2)}</td>
        <td>${l.aplicaIgv ? `${sym} ${l.igv.toFixed(2)}` : '-'}</td>
        <td>${sym} ${l.total.toFixed(2)}</td>
        <td><button class="btn btn-danger btn-sm" onclick="eliminarItem(${idx})"><i class="fas fa-times"></i></button></td>
      </tr>`;
    });
    const r = resumen();
    const totalServ = totalServicios();
    el('resumenGravadas').innerText = `${sym} ${r.gravada.toFixed(2)}`;
    el('resumenExoneradas').innerText = `${sym} ${r.exonerada.toFixed(2)}`;
    el('resumenInafectas').innerText = `${sym} ${r.inafecta.toFixed(2)}`;
    el('resumenExportacion').innerText = `${sym} ${r.exportacion.toFixed(2)}`;
    el('resumenGratuitas').innerText = `${sym} ${r.gratuita.toFixed(2)}`;
    el('resumenServicios').innerText = `${sym} ${totalServ.toFixed(2)}`;
    el('resumenIgv').innerText = `${sym} ${r.igv.toFixed(2)}`;
    el('totalGeneral').innerText = `${sym} ${r.total.toFixed(2)}`;
    validarReglaBoletaDocumento();
    actualizarDetraccionPanel();
    renderReglasOperacion();
    recalcDetraccion();
  }

  window.eliminarItem = function eliminarItem(idx) {
    itemsSeleccionados.splice(idx, 1);
    updateTabla();
  };

  function renderCatalogo() {
    const tipo = el('tipoItemSelector').value;
    const list = tipo === 'servicio' ? serviciosDisponibles : productosDisponibles;
    const sel = el('catalogoItems');
    sel.innerHTML = "<option value=''>Seleccione</option>";
    list.forEach(item => {
      const o = document.createElement('option');
      o.value = `${item.tipo_item}|${item.item_id}`;
      o.text = item.tipo_item === 'producto' ? `${item.descripcion} (Stock: ${item.stock})` : `${item.descripcion} (Servicio)`;
      o.dataset.tipo = item.tipo_item;
      o.dataset.id = item.item_id;
      o.dataset.codigo = item.codigo;
      o.dataset.descripcion = item.descripcion;
      o.dataset.unidad = item.unidad;
      o.dataset.precio = item.precio;
      o.dataset.stock = item.stock ?? '';
      if (item.tipo_item === 'producto' && item.stock <= 0) o.disabled = true;
      sel.appendChild(o);
    });
    setItemSeleccionado();
  }

  function setItemSeleccionado() {
    const o = el('catalogoItems').options[el('catalogoItems').selectedIndex];
    el('precioUnitario').value = o && o.value ? Number(o.dataset.precio || 0).toFixed(2) : '';
  }

  window.agregarItem = function agregarItem() {
    const o = el('catalogoItems').options[el('catalogoItems').selectedIndex];
    if (!o || !o.value) return Swal.fire('Error', 'Seleccione un item', 'error');
    const tipo = o.dataset.tipo;
    const itemId = Number(o.dataset.id);
    const cantidad = Number(el('cantidadItem').value || 0);
    const precio = Number(el('precioUnitario').value || 0);
    const descuento = Number(el('descuentoItem').value || 0);
    const stock = Number(o.dataset.stock || 0);
    if (cantidad <= 0) return Swal.fire('Error', 'Cantidad invalida', 'error');
    if (tipo === 'producto' && cantidad > stock) return Swal.fire('Error', 'Stock insuficiente', 'error');

    const tipAfe = el('tipoAfectacionIgv').value || '10';
    const existing = itemsSeleccionados.find(i => i.tipo_item === tipo && i.item_id === itemId && i.tip_afe_igv === tipAfe);
    if (existing) {
      existing.cantidad += cantidad;
      existing.descuento += descuento;
    } else {
      itemsSeleccionados.push({
        tipo_item: tipo,
        item_id: itemId,
        codigo: o.dataset.codigo,
        descripcion: o.dataset.descripcion,
        unidad: o.dataset.unidad || (tipo === 'servicio' ? 'ZZ' : 'NIU'),
        cantidad,
        valor_unitario: precio,
        descuento,
        tip_afe_igv: tipAfe,
      });
    }
    updateTabla();
  };

  function clearErrors() {
    const box = el('backendValidationBox');
    box.classList.add('d-none');
    box.innerHTML = '';
    document.querySelectorAll('#formRegistroFactura .is-invalid').forEach(n => n.classList.remove('is-invalid'));
  }

  function showBackendErrors(err) {
    clearErrors();
    if (!err || !err.errors) return Swal.fire('Error', err?.message || 'Error al procesar', 'error');
    const map = {
      'fecha_emision': 'fecha_emision', 'forma_pago': 'forma_pago',
      'credito.cuotas': 'credito_cuotas', 'credito.fecha_vencimiento': 'credito_fecha_vencimiento',
      'detraccion.aplica': 'detraccion_aplica',
      'detraccion.codigo': 'detraccion_codigo', 'detraccion.cuenta': 'detraccion_cuenta',
      'detraccion.porcentaje': 'detraccion_porcentaje', 'detraccion.monto': 'detraccion_monto',
      'cliente.tipo_doc': 'cliente_tipo_doc', 'cliente.num_doc': 'cliente_num_doc', 'cliente.razon_social': 'cliente_razon_social'
    };
    const messages = [];
    Object.keys(err.errors).forEach(k => {
      (err.errors[k] || []).forEach(m => messages.push(m));
      if (map[k] && el(map[k])) el(map[k]).classList.add('is-invalid');
    });
    el('backendValidationBox').innerHTML = `<strong>Corrige lo siguiente:</strong><ul class="mb-0 mt-2">${messages.map(m => `<li>${m}</li>`).join('')}</ul>`;
    el('backendValidationBox').classList.remove('d-none');
  }

  function readCliente() {
    const tipoComp = el('tipo_documento').value;
    let tipoDoc = tipoComp === '01' ? '6' : el('cliente_tipo_doc').value;
    let numDoc = el('cliente_num_doc').value.trim();
    let razon = el('cliente_razon_social').value.trim();
    if (tipoComp === '03' && boletaSinDniPermitida() && tipoDoc === '0') {
      numDoc = '';
      if (!razon) razon = 'CLIENTES VARIOS';
    }
    return { tipo_doc: tipoDoc, num_doc: numDoc, razon_social: razon };
  }

  window.limpiarFormularioFactura = function limpiarFormularioFactura() {
    itemsSeleccionados = [];
    detraccionManual = false;
    updateTabla();
    el('cantidadItem').value = 1;
    el('descuentoItem').value = 0;
    el('precioUnitario').value = '';
    el('cliente_num_doc').value = '';
    el('cliente_razon_social').value = '';
    el('cliente_direccion').value = '';
    el('cliente_email').value = '';
    el('cliente_telefono').value = '';
    el('observacion').value = '';
    el('credito_cuotas').value = 1;
    el('credito_monto_pendiente').value = '';
    el('detraccion_base').value = '0.00';
    el('detraccion_monto').value = '0.00';
    clearErrors();
    window.setFechaActual();
    aplicarReglasComprobante();
    aplicarReglasFormaPago();
    syncDetraccionPct();
    recalcDetraccion();
  };

  // Permite cargar una factura rechazada para corregirla y generar un nuevo comprobante.
  window.cargarFacturaDesdePayload = async function cargarFacturaDesdePayload(payload) {
    if (!payload) return;

    if (!productosDisponibles.length && !serviciosDisponibles.length) {
      await window.fetchProductos();
    }

    window.limpiarFormularioFactura();

    el('tipo_documento').value = payload.tipo_documento || '01';
    el('fecha_emision').value = fromServerDateTime(payload.fecha_emision);
    el('moneda').value = payload.moneda || 'PEN';
    el('forma_pago').value = payload.forma_pago || 'contado';
    el('observacion').value = payload.observacion || '';

    const cliente = payload.cliente || {};
    el('cliente_tipo_doc').value = String(cliente.tipo_doc || (el('tipo_documento').value === '01' ? '6' : '1'));
    el('cliente_num_doc').value = cliente.num_doc || '';
    el('cliente_razon_social').value = cliente.razon_social || '';

    if (payload.credito) {
      el('credito_cuotas').value = Number(payload.credito.cuotas || 1);
      el('credito_fecha_vencimiento').value = payload.credito.fecha_vencimiento || '';
      el('credito_monto_pendiente').value = Number(payload.credito.monto_pendiente || 0).toFixed(2);
    }

    const detr = payload.detraccion || {};
    el('detraccion_codigo').value = detr.codigo || el('detraccion_codigo').value;
    syncDetraccionPct();
    el('detraccion_cuenta').value = detr.cuenta || el('detraccion_cuenta').value;
    el('detraccion_aplica').checked = !!detr.aplica;
    el('detraccion_monto').value = Number(detr.monto || 0).toFixed(2);
    detraccionManual = !!detr.aplica;

    itemsSeleccionados = (payload.items || []).map((item) => ({
      tipo_item: item.tipo_item || 'producto',
      item_id: Number(item.item_id || 0),
      codigo: item.codigo || '',
      descripcion: item.descripcion || '',
      unidad: item.unidad || (item.tipo_item === 'servicio' ? 'ZZ' : 'NIU'),
      cantidad: Number(item.cantidad || 0),
      valor_unitario: Number(item.valor_unitario || 0),
      descuento: Number(item.descuento || 0),
      tip_afe_igv: String(item.tip_afe_igv || '10'),
    })).filter((item) => item.cantidad > 0);

    aplicarReglasComprobante();
    aplicarReglasFormaPago();
    updateTabla();
    renderReglasOperacion();
    recalcDetraccion();
  };

  window.procesarFactura = async function procesarFactura() {
    clearErrors();
    if (!itemsSeleccionados.length) return Swal.fire('Error', 'Agrega al menos un item', 'error');
    if (!el('fecha_emision').value) return Swal.fire('Error', 'Fecha de emision requerida', 'error');
    if (!validarReglaBoletaDocumento()) return Swal.fire('Error', 'Revisa el documento del cliente', 'error');

    const tipoComp = el('tipo_documento').value;
    const usaDet = tipoComp === '01'
      && tieneServicios()
      && requiereDetraccionServicios();
    if (usaDet && !el('detraccion_aplica').checked) {
      return Swal.fire('Error', 'Para servicios en factura debes usar detraccion.', 'error');
    }

    const r = resumen();
    const detMonto = Number(el('detraccion_monto').value || 0);
    const payload = {
      tipo_documento: tipoComp,
      fecha_emision: el('fecha_emision').value.replace('T', ' ') + ':00',
      moneda: el('moneda').value,
      forma_pago: el('forma_pago').value,
      observacion: el('observacion').value.trim(),
      cliente: readCliente(),
      items: itemsSeleccionados,
      credito: el('forma_pago').value === 'credito' ? {
        cuotas: Number(el('credito_cuotas').value || 1),
        fecha_vencimiento: el('credito_fecha_vencimiento').value,
        monto_pendiente: Number(el('credito_monto_pendiente').value || Math.max(r.total - detMonto, 0)),
      } : null,
      detraccion: {
        aplica: usaDet,
        codigo: usaDet ? el('detraccion_codigo').value : null,
        porcentaje: usaDet ? Number(el('detraccion_porcentaje').value || 0) : null,
        cuenta: usaDet ? el('detraccion_cuenta').value.trim() : null,
        medio_pago: usaDet ? DETRACCION_MEDIO_PAGO : null,
        monto: usaDet ? detMonto : null,
      },
    };

    try {
      await apiFetch('/api/factura/nuevaventa', { method: 'POST', body: JSON.stringify(payload) });
      Swal.fire('OK', 'Factura registrada y enviada a proceso', 'success');
      $('#modalFactura').modal('hide');
      window.limpiarFormularioFactura();
    } catch (err) {
      showBackendErrors(err);
    }
  };

  window.fetchProductos = async function fetchProductos() {
    try {
      const [prod, serv] = await Promise.all([
        apiFetch('/api/productos?per_page=200'),
        apiFetch('/api/servicios?per_page=200'),
      ]);
      productosDisponibles = (prod.data || []).map(p => ({
        tipo_item: 'producto', item_id: Number(p.id), codigo: p.codigo, descripcion: p.descripcion,
        unidad: p.unidad || 'NIU', precio: Number(p.precio || 0), stock: Number(p.stock || 0), activo: Number(p.activo || 0) === 1
      })).filter(p => p.activo);
      serviciosDisponibles = (serv.data || []).map(s => ({
        tipo_item: 'servicio', item_id: Number(s.id), codigo: `SERV-${s.id}`, descripcion: s.nombre || s.descripcion || `Servicio ${s.id}`,
        unidad: 'ZZ', precio: Number(s.precio || 0), stock: null, activo: !!s.activo
      })).filter(s => s.activo);
      renderCatalogo();
    } catch (e) {
      Swal.fire('Error', 'No se pudo cargar productos/servicios', 'error');
    }
  };

  async function buscarCliente(doc) {
    const tipoDoc = el('cliente_tipo_doc').value;
    if (tipoDoc === '0') return;
    try {
      const res = await apiFetch(`/api/clientes?search=${doc}`);
      const c = (res.data || []).find(x => x.num_doc === doc);
      if (!c) return;
      el('cliente_razon_social').value = c.razon_social || '';
      el('cliente_direccion').value = c.direccion || '';
      el('cliente_email').value = c.email || '';
      el('cliente_telefono').value = c.telefono || '';
      el('clienteEstado').className = 'form-text text-success';
      el('clienteEstado').innerHTML = 'Cliente encontrado';
    } catch (e) {}
  }

  function init() {
    if (!el('formRegistroFactura')) return;
    window.setFechaActual();
    aplicarReglasComprobante();
    aplicarReglasFormaPago();
    renderAfectacionInfo();
    renderReglasOperacion();
    syncDetraccionPct();
    el('detraccion_aplica').checked = false;
    recalcDetraccion();

    el('tipo_documento').addEventListener('change', aplicarReglasComprobante);
    el('forma_pago').addEventListener('change', aplicarReglasFormaPago);
    el('tipoAfectacionIgv').addEventListener('change', renderAfectacionInfo);
    el('moneda').addEventListener('change', () => { updateTabla(); recalcDetraccion(); });
    el('tipoItemSelector').addEventListener('change', renderCatalogo);
    el('catalogoItems').addEventListener('change', setItemSeleccionado);
    el('detraccion_codigo').addEventListener('change', () => {
      detraccionManual = false;
      syncDetraccionPct();
      recalcDetraccion();
    });
    el('detraccion_aplica').addEventListener('change', () => {
      if (!el('detraccion_aplica').checked) detraccionManual = false;
      recalcDetraccion();
    });
    el('detraccion_monto').addEventListener('input', function () {
      if (this.disabled) return;
      detraccionManual = true;
    });
    el('cliente_tipo_doc').addEventListener('change', validarReglaBoletaDocumento);
    el('cliente_num_doc').addEventListener('keyup', function () {
      const doc = this.value.trim();
      const tipoDoc = el('tipo_documento').value === '01' ? '6' : el('cliente_tipo_doc').value;
      const min = tipoDoc === '0' ? 999 : (tipoDoc === '6' ? 11 : 8);
      clearTimeout(timeoutCliente);
      if (doc.length < min) return;
      timeoutCliente = setTimeout(() => buscarCliente(doc), 450);
    });
  }

  document.addEventListener('DOMContentLoaded', init);
})();
