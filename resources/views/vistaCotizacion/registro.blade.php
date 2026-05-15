@extends('admin.main')

@section('contenido')
<div class="content">
    <div class="container-fluid">
        <style>
            .cot-chip {
                border: 1px solid #ced4da;
                border-radius: 999px;
                padding: 6px 12px;
                background: #fff;
                font-size: 13px;
                margin: 0 6px 6px 0;
                transition: all .2s ease;
            }

            .cot-chip:hover {
                border-color: #007bff;
                color: #007bff;
            }

            .cot-chip.active {
                background: #007bff;
                border-color: #007bff;
                color: #fff;
            }

            .cot-soft-box {
                border: 1px dashed #ced4da;
                border-radius: 10px;
                padding: 12px;
                background: #f8f9fa;
            }

            .cot-mode-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                border-radius: 999px;
                background: #e9f2ff;
                color: #0b5ed7;
                font-size: 13px;
                font-weight: 600;
            }

            .cot-service-steps {
                margin: 6px 0 0 18px;
                padding: 0;
                font-size: 12px;
                color: #495057;
            }

            .cot-detail-editor {
                margin-top: 8px;
            }

            .cot-detail-editor textarea {
                min-height: 88px;
                font-size: 12px;
                resize: vertical;
            }

            .cot-summary-card {
                position: sticky;
                top: 12px;
            }

            .cot-summary-body {
                max-height: calc(100vh - 150px);
                overflow-y: auto;
                padding-right: 8px;
            }

            .cot-summary-body::-webkit-scrollbar {
                width: 8px;
            }

            .cot-summary-body::-webkit-scrollbar-thumb {
                background: #c9d3dd;
                border-radius: 999px;
            }

            .cot-client-preview {
                margin-top: 10px;
                padding: 10px 12px;
                border: 1px solid #dee2e6;
                border-radius: 10px;
                background: #f8f9fa;
                font-size: 12px;
            }

            .cot-client-preview strong {
                display: block;
                color: #1f2d3d;
            }

            .cot-client-preview span {
                display: block;
                color: #6c757d;
                margin-top: 2px;
            }

            .cot-client-search {
                position: relative;
            }

            .cot-client-results {
                display: none;
                position: absolute;
                top: calc(100% + 6px);
                left: 0;
                right: 0;
                z-index: 20;
                max-height: 260px;
                overflow-y: auto;
                border: 1px solid #dee2e6;
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 12px 25px rgba(31, 45, 61, 0.12);
            }

            .cot-client-results.show {
                display: block;
            }

            .cot-client-results-title {
                padding: 8px 12px;
                font-size: 11px;
                font-weight: 700;
                color: #6c757d;
                background: #f8f9fa;
                border-bottom: 1px solid #eef1f4;
                text-transform: uppercase;
                letter-spacing: .4px;
            }

            .cot-client-option {
                width: 100%;
                border: 0;
                border-bottom: 1px solid #f1f3f5;
                background: #fff;
                text-align: left;
                padding: 10px 12px;
            }

            .cot-client-option:last-child {
                border-bottom: 0;
            }

            .cot-client-option:hover {
                background: #f8fbff;
            }

            .cot-client-option strong,
            .cot-client-option span {
                display: block;
            }

            .cot-client-option span {
                font-size: 12px;
                color: #6c757d;
                margin-top: 2px;
            }

            .cot-client-link {
                margin-top: 8px;
            }

            .cot-quick-actions .btn {
                min-width: 220px;
            }

            @media (max-width: 991.98px) {
                .cot-summary-card {
                    position: static;
                }

                .cot-summary-body {
                    max-height: none;
                    overflow: visible;
                    padding-right: 0;
                }
            }
        </style>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-0 font-weight-bold" id="tituloFormulario">
                    <i class="fas fa-file-signature text-primary"></i> Registrar Cotizacion
                </h3>
                <small class="text-muted" id="subtituloFormulario">Agrega productos o servicios y arma la cotizacion con ayuda de plantillas.</small>
            </div>

            <div class="d-flex">
                <button type="button"
                    id="btnPdf"
                    class="btn btn-danger mr-2"
                    style="display:none;"
                    onclick="window.open(`/cotizaciones/pdf/${editingCotizacionId}`, '_blank')">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </button>

                <a href="/cotizaciones" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Agregar producto o servicio</strong>
                        <span id="modoCotizacion" class="cot-mode-badge">
                            <i class="fas fa-layer-group"></i> Cotizacion vacia
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label class="mb-1">Tipo de busqueda</label>
                                <select id="tipo" class="form-control">
                                    <option value="producto">Producto</option>
                                    <option value="servicio">Servicio</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="mb-1">Buscar</label>
                                <input type="text"
                                    id="buscar"
                                    class="form-control"
                                    placeholder="Escribe el nombre del producto o servicio...">
                            </div>
                        </div>

                        <div id="resultados" class="mb-4">
                            <div class="alert alert-light border text-muted mb-0">
                                Escribe al menos 2 letras para buscar.
                            </div>
                        </div>
                        <small class="text-muted d-block mb-3">La cotizacion se emite en una sola moneda (PEN o USD). Si agregas un item en otra moneda, el sistema pedira tipo de cambio para convertirlo.</small>

                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 12%;">Tipo</th>
                                        <th>Detalle</th>
                                        <th style="width: 14%;">Cantidad</th>
                                        <th style="width: 10%;">Moneda</th>
                                        <th style="width: 14%;">Precio</th>
                                        <th style="width: 14%;">Subtotal</th>
                                        <th style="width: 8%;"></th>
                                    </tr>
                                </thead>

                                <tbody id="itemsTable">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            Aun no agregaste items
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 cot-summary-card">
                    <div class="card-header">
                        <strong>Resumen y condiciones</strong>
                    </div>

                    <div class="card-body cot-summary-body">
                        <div class="form-group">
                            <label for="cliente_id">Cliente *</label>
                            <input type="hidden" id="cliente_id">
                            <div class="cot-client-search">
                                <input type="text"
                                    id="cliente_search"
                                    class="form-control"
                                    placeholder="Buscar cliente por nombre o documento...">
                                <div id="clienteResults" class="cot-client-results"></div>
                            </div>
                            <button type="button"
                                class="btn btn-sm btn-outline-primary cot-client-link"
                                data-toggle="modal"
                                data-target="#modalClienteRapido">
                                <i class="fas fa-user-plus"></i> Registrar cliente nuevo
                            </button>
                            <small class="text-muted d-block mt-2">Al abrir la busqueda se muestran los 20 clientes mas recientes.</small>
                            <div id="clientePreview" class="cot-client-preview text-muted">
                                Selecciona un cliente para ver su documento y direccion.
                            </div>
                            <div class="invalid-feedback d-block" id="error_cliente_id"></div>
                        </div>

                        <div class="form-group">
                            <label for="asunto">Asunto</label>
                            <input type="text" id="asunto" class="form-control">
                            <div class="invalid-feedback d-block" id="error_asunto"></div>
                        </div>

                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" id="fecha" class="form-control">
                            <div class="invalid-feedback d-block" id="error_fecha"></div>
                        </div>

                        <div class="d-none">
                            <select id="moneda" class="form-control">
                                <option value="PEN">Soles (PEN)</option>
                                <option value="USD">Dolares (USD)</option>
                            </select>
                            <div class="invalid-feedback d-block" id="error_moneda"></div>
                        </div>

                        <div class="form-group">
                            <label for="tipo_cambio">Tipo de cambio (opcional)</label>
                            <input type="text" id="tipo_cambio" class="form-control" inputmode="decimal" placeholder="Ejemplo: 3.8000 o 3,8000">
                            <small class="text-muted">Solo se usa si necesitas convertir entre PEN y USD.</small>
                            <div class="invalid-feedback d-block" id="error_tipo_cambio"></div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion_general">Descripcion general</label>
                            <textarea id="descripcion_general" class="form-control" rows="3" placeholder="Resumen general del alcance de la cotizacion"></textarea>
                            <div class="invalid-feedback d-block" id="error_descripcion_general"></div>
                        </div>

                        <div class="form-group">
                            <label for="incluye_igv">Tratamiento de IGV</label>
                            <select id="incluye_igv" class="form-control">
                                <option value="1">Incluye IGV (18%)</option>
                                <option value="0">No incluye IGV</option>
                            </select>
                            <small class="text-muted">Este campo controla el calculo oficial y el texto del PDF.</small>
                            <div class="invalid-feedback d-block" id="error_incluye_igv"></div>
                        </div>

                        <div class="cot-soft-box mb-3">
                            <div class="font-weight-bold mb-2">Notas sugeridas</div>
                            <div class="mb-2">
                                <button type="button" class="cot-chip" data-note-key="igv_no" onclick="togglePresetNote('igv_no', 'No incluye IGV')">
                                    No incluye IGV
                                </button>
                                <button type="button" class="cot-chip" data-note-key="igv_si" onclick="togglePresetNote('igv_si', 'Incluye IGV')">
                                    Incluye IGV
                                </button>
                                <button type="button" class="cot-chip" data-note-key="todo_costo" onclick="togglePresetNote('todo_costo', 'El servicio se ejecuta a todo costo')">
                                    A todo costo
                                </button>
                                <button type="button" class="cot-chip" data-note-key="sctr" onclick="togglePresetNote('sctr', 'Incluye SCTR')">
                                    Incluye SCTR
                                </button>
                                <button type="button" class="cot-chip" data-note-key="protocolos" onclick="togglePresetNote('protocolos', 'Incluye protocolos requeridos')">
                                    Incluye protocolos
                                </button>
                                <button type="button" class="cot-chip" data-note-key="facilidades" onclick="togglePresetNote('facilidades', 'El cliente debe brindar facilidades para la ejecucion')">
                                    Requiere facilidades
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <label class="mb-1">Validez</label>
                                    <select id="presetValidez" class="form-control">
                                        <option value="">Sin definir</option>
                                        <option value="Validez de la oferta: 3 dias">3 dias</option>
                                        <option value="Validez de la oferta: 7 dias">7 dias</option>
                                        <option value="Validez de la oferta: 15 dias">15 dias</option>
                                        <option value="Validez de la oferta: 30 dias">30 dias</option>
                                    </select>
                                </div>

                                <div class="col-6">
                                    <label class="mb-1">Tiempo estimado</label>
                                    <select id="presetDuracion" class="form-control">
                                        <option value="">Sin definir</option>
                                        <option value="Duracion de trabajos: 1 dia">1 dia</option>
                                        <option value="Duracion de trabajos: 3 dias">3 dias</option>
                                        <option value="Duracion de trabajos: 7 dias">7 dias</option>
                                        <option value="Duracion de trabajos: 15 dias">15 dias</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notas">Notas finales</label>
                            <textarea id="notas" class="form-control" rows="6" placeholder="Puedes escribir notas libres o usar las sugerencias de arriba"></textarea>
                            <small class="text-muted">Las notas sugeridas se agregan automaticamente aqui y puedes editar el texto libremente.</small>
                            <div class="invalid-feedback d-block" id="error_notas"></div>
                        </div>

                        <div class="form-group">
                            <label class="mb-2">Medios de pago para el PDF</label>
                            <div class="border rounded bg-light p-3">
                                @foreach(config('empresa.medios_pago', []) as $key => $medio)
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox"
                                            class="custom-control-input cot-medio-pago"
                                            id="medio_pago_{{ $key }}"
                                            value="{{ $key }}"
                                            checked>
                                        <label class="custom-control-label" for="medio_pago_{{ $key }}">
                                            <span class="font-weight-bold" style="color: {{ $medio['color'] ?? '#1f2937' }};">
                                                {{ $medio['label'] }}
                                            </span>
                                            <small class="d-block text-muted">{{ $medio['detalle'] }}</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-2">Puedes marcar solo las cuentas que quieras mostrar en esta cotizacion.</small>
                            <div class="invalid-feedback d-block" id="error_medios_pago"></div>
                        </div>

                        <div class="form-group" id="grupoEstado" style="display:none;">
                            <label for="estado">Estado</label>
                            <select id="estado" class="form-control">
                                <option value="borrador">Borrador</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                            <div class="invalid-feedback d-block" id="error_estado"></div>
                        </div>

                        <hr>

                        <p class="mb-1">Moneda: <strong id="monedaResumen">PEN</strong></p>
                        <p class="mb-1">Subtotal: <strong id="subtotal">S/ 0.00</strong></p>
                        <p class="mb-1"><span id="igvLabel">IGV (18%):</span> <strong id="igv">S/ 0.00</strong></p>
                        <h4 class="mt-2">Total: <span id="total">S/ 0.00</span></h4>

                        <button id="btnGuardar" class="btn btn-success btn-block mt-3">
                            Guardar cotizacion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalClienteRapido">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus"></i> Registrar Cliente
                </h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Tipo Documento</label>
                        <select class="form-control" id="quick_tipo_doc">
                            <option value="1">DNI</option>
                            <option value="6">RUC</option>
                        </select>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label>N° Documento</label>
                        <input type="text" class="form-control" id="quick_num_doc">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Razón Social</label>
                        <input type="text" class="form-control" id="quick_razon_social">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Teléfono</label>
                        <input type="text" class="form-control" id="quick_telefono">
                    </div>

                    <div class="col-md-8 mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" id="quick_email">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Dirección</label>
                        <input type="text" class="form-control" id="quick_direccion">
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" id="btnGuardarClienteRapido">
                    Guardar cliente
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProductoRapido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-box-open"></i> Registrar Producto
                </h5>
                <button class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label>Descripcion *</label>
                        <input type="text" class="form-control" id="quick_producto_descripcion">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Categoria</label>
                        <input type="text" class="form-control" id="quick_producto_categoria" placeholder="General">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Unidad</label>
                        <input type="text" class="form-control" id="quick_producto_unidad" value="NIU" maxlength="10">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Precio *</label>
                        <input type="number" class="form-control" id="quick_producto_precio" min="0" step="0.01">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Moneda *</label>
                        <select class="form-control" id="quick_producto_moneda_precio">
                            <option value="PEN">Soles (PEN)</option>
                            <option value="USD">Dolares (USD)</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Stock *</label>
                        <input type="number" class="form-control" id="quick_producto_stock" min="0" step="0.01" value="0">
                    </div>
                </div>

                <small class="text-muted d-block text-center">Se registrara y se agregara al instante a la cotizacion.</small>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarProductoRapido">
                    Guardar y agregar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalServicioRapido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-concierge-bell"></i> Registrar Servicio
                </h5>
                <button class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nombre *</label>
                        <input type="text" class="form-control" id="quick_servicio_nombre">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Precio *</label>
                        <input type="number" class="form-control" id="quick_servicio_precio" min="0" step="0.01">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Moneda *</label>
                        <select class="form-control" id="quick_servicio_moneda_precio">
                            <option value="PEN">Soles (PEN)</option>
                            <option value="USD">Dolares (USD)</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Descripcion</label>
                        <textarea class="form-control" id="quick_servicio_descripcion" rows="3" placeholder="Describe brevemente el alcance del servicio"></textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tipo de servicio</label>
                        <input type="text" class="form-control" id="quick_servicio_tipo" placeholder="Instalacion, mantenimiento, calibracion...">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Nivel</label>
                        <select class="form-control" id="quick_servicio_nivel">
                            <option value="">Sin definir</option>
                            <option value="basico">Basico</option>
                            <option value="estandar">Estandar</option>
                            <option value="premium">Premium</option>
                        </select>
                    </div>
                </div>

                <small class="text-muted d-block text-center">Luego podras completar el detalle del servicio desde la tabla de items.</small>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btnGuardarServicioRapido">
                    Guardar y agregar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let items = [];
let cargando = false;
let resultadoBusqueda = [];
let debounceItems;
let debounceCliente;
let clientesRecientes = [];
let clienteOptions = [];
let selectedCliente = null;
let selectedCurrency = 'PEN';
let lastExchangeRate = 3.80;
const mediosPagoDisponibles = @json(config('empresa.medios_pago', []));

const params = new URLSearchParams(window.location.search);
const editingCotizacionId = params.get('id');
const presetKeys = {
    igv_no: 'No incluye IGV',
    igv_si: 'Incluye IGV',
    todo_costo: 'El servicio se ejecuta a todo costo',
    sctr: 'Incluye SCTR',
    protocolos: 'Incluye protocolos requeridos',
    facilidades: 'El cliente debe brindar facilidades para la ejecucion'
};

function incluyeIgvSeleccionado() {
    return $('#incluye_igv').val() !== '0';
}

function syncIgvNotesWithSelector() {
    const incluyeIgv = incluyeIgvSeleccionado();

    removeNoteLine(incluyeIgv ? presetKeys.igv_no : presetKeys.igv_si);
    addNoteLine(incluyeIgv ? presetKeys.igv_si : presetKeys.igv_no);
    syncPresetControlsFromNotes();
    calc();
}

function normalizeText(value) {
    return String(value || '')
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function resolveIncluyeIgvFromNotes(notes = []) {
    let decision = null;

    notes.forEach((line) => {
        const normalized = normalizeText(line);

        if (normalized.includes('no incluye igv')) {
            decision = false;
            return;
        }

        if (normalized.includes('incluye igv')) {
            decision = true;
        }
    });

    return decision;
}

function normalizeCurrency(code) {
    return String(code || 'PEN').toUpperCase() === 'USD' ? 'USD' : 'PEN';
}

function currencySymbol(code) {
    return normalizeCurrency(code) === 'USD' ? 'US$' : 'S/';
}

function formatCurrency(value, code = 'PEN') {
    return `${currencySymbol(code)} ${Number(value || 0).toFixed(2)}`;
}

function getQuoteCurrency() {
    return normalizeCurrency($('#moneda').val() || selectedCurrency || 'PEN');
}

function setQuoteCurrency(code) {
    selectedCurrency = normalizeCurrency(code);
    $('#moneda').val(selectedCurrency);
}

function getExchangeRate() {
    const rate = parseDecimalInput($('#tipo_cambio').val());
    if (Number.isFinite(rate) && rate > 0) {
        lastExchangeRate = rate;
        return rate;
    }

    return null;
}

function parseDecimalInput(value) {
    if (typeof value === 'number') {
        return Number.isFinite(value) ? value : NaN;
    }

    const normalized = String(value ?? '')
        .trim()
        .replace(/\s+/g, '')
        .replace(',', '.');

    if (!normalized) {
        return NaN;
    }

    const parsed = Number(normalized);
    return Number.isFinite(parsed) ? parsed : NaN;
}

function convertAmount(value, fromCurrency, toCurrency, exchangeRate) {
    const from = normalizeCurrency(fromCurrency);
    const to = normalizeCurrency(toCurrency);
    const amount = Number(value || 0);
    const rate = parseDecimalInput(exchangeRate);

    if (!Number.isFinite(amount)) {
        return NaN;
    }

    if (from === to) {
        return amount;
    }

    if (!Number.isFinite(rate) || rate <= 0) {
        return NaN;
    }

    if (from === 'USD' && to === 'PEN') {
        return amount * rate;
    }

    if (from === 'PEN' && to === 'USD') {
        return amount / rate;
    }

    return amount;
}

async function pedirTipoCambio(fromCurrency, toCurrency, contexto = 'conversion') {
    const defaults = Number(getExchangeRate() || lastExchangeRate || 3.8).toFixed(4);

    const result = await Swal.fire({
        title: 'Tipo de cambio requerido',
        html: `Convertir de <strong>${fromCurrency}</strong> a <strong>${toCurrency}</strong> para ${contexto}.`,
        input: 'text',
        inputValue: defaults,
        inputPlaceholder: 'Ejemplo: 3.8000 o 3,8000',
        showCancelButton: true,
        confirmButtonText: 'Aplicar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            const rate = parseDecimalInput(value);
            if (!Number.isFinite(rate) || rate <= 0) {
                return 'Ingresa un tipo de cambio valido mayor a 0.';
            }

            return null;
        }
    });

    if (!result.isConfirmed) {
        return null;
    }

    const rate = parseDecimalInput(result.value);
    if (!Number.isFinite(rate) || rate <= 0) {
        return null;
    }

    lastExchangeRate = rate;
    $('#tipo_cambio').val(rate.toFixed(4));
    $('#error_tipo_cambio').text('');
    return rate;
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function limpiarErrores() {
    $('.is-invalid').removeClass('is-invalid');
    $('[id^="error_"]').text('');
}

function mostrarErrores(errors = {}) {
    limpiarErrores();

    Object.keys(errors).forEach(key => {
        const baseKey = key.replace(/\.\d+\./g, '_').replace(/\./g, '_');
        const feedback = $(`#error_${baseKey}`);

        if (feedback.length) {
            feedback.text(errors[key][0] || 'Dato invalido');
        }
    });

    if (errors.items) {
        Swal.fire('Error', errors.items[0], 'error');
        return;
    }

    const firstKey = Object.keys(errors)[0];

    if (firstKey && firstKey.startsWith('items.')) {
        Swal.fire('Error', errors[firstKey][0] || 'Revisa los items de la cotizacion', 'error');
    }
}

function getNoteLines() {
    return $('#notas').val()
        .split(/\r?\n/)
        .map(line => line.trim())
        .filter(Boolean);
}

function setNoteLines(lines) {
    $('#notas').val([...new Set(lines.filter(Boolean))].join('\n'));
}

function addNoteLine(line) {
    const lines = getNoteLines();

    if (!lines.includes(line)) {
        lines.push(line);
        setNoteLines(lines);
    }
}

function removeNoteLine(line) {
    setNoteLines(getNoteLines().filter(item => item !== line));
}

function togglePresetNote(key, line) {
    const button = $(`[data-note-key="${key}"]`);
    const isActive = button.hasClass('active');

    if (key === 'igv_no' || key === 'igv_si') {
        $('#incluye_igv').val(key === 'igv_si' ? '1' : '0');
        removeNoteLine(presetKeys.igv_no);
        removeNoteLine(presetKeys.igv_si);
        addNoteLine(line);
        syncPresetControlsFromNotes();
        calc();
        return;
    }

    button.toggleClass('active', !isActive);

    if (isActive) {
        removeNoteLine(line);
    } else {
        addNoteLine(line);
    }
}

function upsertSelectNote(prefix, value) {
    const lines = getNoteLines().filter(line => !line.startsWith(prefix));

    if (value) {
        lines.push(value);
    }

    setNoteLines(lines);
}

function syncPresetControlsFromNotes() {
    const notes = getNoteLines();
    const incluyeIgvDesdeNotas = resolveIncluyeIgvFromNotes(notes);

    if (incluyeIgvDesdeNotas !== null) {
        $('#incluye_igv').val(incluyeIgvDesdeNotas ? '1' : '0');
    }

    Object.entries(presetKeys).forEach(([key, text]) => {
        if (key === 'igv_no' || key === 'igv_si') {
            return;
        }

        $(`[data-note-key="${key}"]`).toggleClass('active', notes.includes(text));
    });

    const incluyeIgv = incluyeIgvSeleccionado();
    $(`[data-note-key="igv_no"]`).toggleClass('active', !incluyeIgv);
    $(`[data-note-key="igv_si"]`).toggleClass('active', incluyeIgv);

    $('#presetValidez').val(notes.find(line => line.startsWith('Validez de la oferta:')) || '');
    $('#presetDuracion').val(notes.find(line => line.startsWith('Duracion de trabajos:')) || '');
    calc();
}

function getModoCotizacion() {
    const hasProductos = items.some(item => item.tipo === 'producto');
    const hasServicios = items.some(item => item.tipo === 'servicio');

    if (!items.length) {
        return {
            key: 'vacia',
            label: 'Cotizacion vacia'
        };
    }

    if (hasProductos && hasServicios) {
        return {
            key: 'mixta',
            label: 'Cotizacion mixta'
        };
    }

    if (hasServicios) {
        return {
            key: 'servicios',
            label: 'Cotizacion de servicios'
        };
    }

    return {
        key: 'productos',
        label: 'Cotizacion de productos'
    };
}

function renderClientePreview(cliente) {
    if (!cliente) {
        selectedCliente = null;
        $('#cliente_id').val('');
        $('#clientePreview').html('Selecciona un cliente para ver su documento y direccion.');
        return;
    }

    selectedCliente = cliente;
    $('#cliente_id').val(cliente.id);
    $('#clientePreview').html(`
        <strong>${escapeHtml(cliente.razon_social || 'Cliente seleccionado')}</strong>
        <span>${escapeHtml(cliente.num_doc ? `Documento: ${cliente.num_doc}` : 'Sin documento')}</span>
        <span>${escapeHtml(cliente.direccion || 'Sin direccion registrada')}</span>
    `);
}

function renderClienteOptions(clientes, title = 'Resultados') {
    clienteOptions = clientes;

    if (!clientes.length) {
        $('#clienteResults')
            .addClass('show')
            .html(`
                <div class="cot-client-results-title">${title}</div>
                <div class="p-3 text-muted small">No se encontraron clientes.</div>
            `);
        return;
    }

    let html = `<div class="cot-client-results-title">${title}</div>`;

    clientes.forEach((cliente, index) => {
        html += `
            <button type="button" class="cot-client-option" onclick="seleccionarCliente(${index})">
                <strong>${escapeHtml(cliente.razon_social)}</strong>
                <span>${escapeHtml(cliente.num_doc || 'Sin documento')}</span>
                <span>${escapeHtml(cliente.direccion || 'Sin direccion registrada')}</span>
            </button>
        `;
    });

    $('#clienteResults').addClass('show').html(html);
}

function hideClienteResults() {
    $('#clienteResults').removeClass('show').empty();
}

function seleccionarCliente(index) {
    const cliente = clienteOptions[index];

    if (!cliente) {
        return;
    }

    $('#cliente_search').val(cliente.razon_social || '');
    renderClientePreview(cliente);
    hideClienteResults();
    $('#cliente_search').removeClass('is-invalid');
}

async function cargarClientesRecientes() {
    const resp = await apiFetch('/api/clientes?per_page=20');
    clientesRecientes = resp.data || [];
}

async function buscarClientes() {
    const term = $('#cliente_search').val().trim();

    if (!term) {
        renderClienteOptions(clientesRecientes, 'Clientes recientes');
        return;
    }

    try {
        const resp = await apiFetch(`/api/clientes?search=${encodeURIComponent(term)}&per_page=20`);
        renderClienteOptions(resp.data || [], 'Coincidencias');
    } catch (error) {
        $('#clienteResults')
            .addClass('show')
            .html('<div class="p-3 text-danger small">No se pudo buscar clientes.</div>');
    }
}

async function guardarClienteRapido() {
    try {
        const resp = await apiFetch('/api/clientes', {
            method: 'POST',
            body: JSON.stringify({
                tipo_doc: $('#quick_tipo_doc').val(),
                num_doc: $('#quick_num_doc').val(),
                razon_social: $('#quick_razon_social').val(),
                telefono: $('#quick_telefono').val(),
                email: $('#quick_email').val(),
                direccion: $('#quick_direccion').val(),
                estado: true
            })
        });

        const cliente = resp.data;
        clientesRecientes = [cliente, ...clientesRecientes.filter(item => item.id !== cliente.id)].slice(0, 20);
        $('#modalClienteRapido').modal('hide');
        $('#cliente_search').val(cliente.razon_social || '');
        renderClientePreview(cliente);
        hideClienteResults();
        Swal.fire('OK', resp.message, 'success');
    } catch (err) {
        Swal.fire('Error', err.message || 'No se pudo registrar el cliente', 'error');
    }
}

function agregarItemCreado(tipo, source) {
    const nombre = source.descripcion || source.nombre || '';

    $('#tipo').val(tipo);
    $('#buscar').val(nombre);

    resultadoBusqueda = [{
        ...source,
        tipo,
        pasos: Array.isArray(source.pasos) ? source.pasos : []
    }];

    renderResultados();
    addItem(tipo, source.id);
}

async function guardarProductoRapido() {
    try {
        const resp = await apiFetch('/api/productos', {
            method: 'POST',
            body: JSON.stringify({
                descripcion: $('#quick_producto_descripcion').val().trim(),
                categoria: $('#quick_producto_categoria').val().trim(),
                unidad: $('#quick_producto_unidad').val().trim() || 'NIU',
                precio: $('#quick_producto_precio').val(),
                moneda_precio: $('#quick_producto_moneda_precio').val(),
                stock: $('#quick_producto_stock').val()
            })
        });

        $('#modalProductoRapido').modal('hide');
        agregarItemCreado('producto', resp.data);
        Swal.fire('OK', resp.message, 'success');
    } catch (err) {
        const message = err.errors
            ? Object.values(err.errors).flat()[0]
            : (err.message || 'No se pudo registrar el producto');
        Swal.fire('Error', message, 'error');
    }
}

async function guardarServicioRapido() {
    try {
        const resp = await apiFetch('/api/servicios', {
            method: 'POST',
            body: JSON.stringify({
                nombre: $('#quick_servicio_nombre').val().trim(),
                precio: $('#quick_servicio_precio').val(),
                moneda_precio: $('#quick_servicio_moneda_precio').val(),
                descripcion: $('#quick_servicio_descripcion').val().trim(),
                tipo_servicio: $('#quick_servicio_tipo').val().trim(),
                nivel_servicio: $('#quick_servicio_nivel').val() || null
            })
        });

        $('#modalServicioRapido').modal('hide');
        agregarItemCreado('servicio', resp.data);
        Swal.fire('OK', resp.message, 'success');
    } catch (err) {
        const message = err.errors
            ? Object.values(err.errors).flat()[0]
            : (err.message || 'No se pudo registrar el servicio');
        Swal.fire('Error', message, 'error');
    }
}

function updateModoCotizacion() {
    const mode = getModoCotizacion();
    $('#modoCotizacion').html(`<i class="fas fa-layer-group"></i> ${mode.label}`);

    if (!$('#asunto').val().trim() || $('#asunto').data('auto-generated') === true) {
        let asunto = 'Cotizacion';

        if (mode.key === 'productos') {
            asunto = 'Cotizacion de productos';
        } else if (mode.key === 'servicios') {
            asunto = 'Cotizacion de servicios';
        } else if (mode.key === 'mixta') {
            asunto = 'Cotizacion de productos y servicios';
        }

        $('#asunto').val(asunto).data('auto-generated', true);
    }
}

function renderResultados() {
    const texto = $('#buscar').val().trim();

    if (texto.length < 2) {
        $('#resultados').html(`
            <div class="alert alert-light border text-muted mb-0">
                Escribe al menos 2 letras para buscar.
            </div>
        `);
        return;
    }

    if (!resultadoBusqueda.length) {
        $('#resultados').html(`
            <div class="alert alert-light border text-muted mb-0">
                No se encontraron resultados para la busqueda actual.
            </div>
        `);
        return;
    }

    let html = '<div class="list-group">';

    resultadoBusqueda.forEach(item => {
        const nombre = item.descripcion || item.nombre || '-';
        const subtitulo = item.tipo === 'servicio'
            ? '<small class="text-muted d-block">Servicio</small>'
            : '<small class="text-muted d-block">Producto</small>';
        const previewPasos = item.tipo === 'servicio' && Array.isArray(item.pasos) && item.pasos.length
            ? `<small class="text-muted d-block mt-1">Incluye ${item.pasos.length} paso(s) definidos</small>`
            : '';
        const monedaItem = normalizeCurrency(item.moneda_precio || 'PEN');

        html += `
            <button type="button" class="list-group-item list-group-item-action"
                onclick="addItem('${item.tipo}', ${item.id})">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-left">
                        <strong>${escapeHtml(nombre)}</strong>
                        ${subtitulo}
                        ${previewPasos}
                    </div>
                    <div class="text-right">
                        <span class="badge badge-primary">${formatCurrency(item.precio, monedaItem)}</span>
                        <small class="text-muted d-block">${monedaItem}</small>
                    </div>
                </div>
            </button>
        `;
    });

    html += '</div>';
    $('#resultados').html(html);
}

async function addItem(tipo, id) {
    const source = resultadoBusqueda.find(item =>
        item.tipo === tipo && Number(item.id) === Number(id)
    );

    if (!source) {
        Swal.fire('Error', 'No se encontro el item seleccionado', 'error');
        return;
    }

    const monedaSource = normalizeCurrency(source.moneda_precio || 'PEN');
    const precioFinal = Number(source.precio || 0);
    const monedaFinal = monedaSource;

    const existing = items.find(item =>
        item.tipo === tipo &&
        Number(item.producto_id || item.servicio_id) === Number(id) &&
        normalizeCurrency(item.moneda_precio || 'PEN') === monedaFinal &&
        Math.abs(Number(item.precio || 0) - Number(precioFinal || 0)) < 0.0001
    );

    if (existing) {
        existing.cantidad += 1;
        renderItems();
        return;
    }

    items.push({
        tipo,
        producto_id: tipo === 'producto' ? source.id : null,
        servicio_id: tipo === 'servicio' ? source.id : null,
        nombre: source.descripcion || source.nombre || '',
        precio: Number(precioFinal || 0),
        moneda_precio: monedaFinal,
        cantidad: 1,
        detalle_servicio: tipo === 'servicio'
            ? (Array.isArray(source.pasos) ? source.pasos.map(step => step.descripcion).filter(Boolean) : [])
            : []
    });

    renderItems();
}

function updateCantidad(index, value) {
    const cantidad = parseDecimalInput(value);

    items[index].cantidad = !cantidad || cantidad < 1 ? 1 : cantidad;
    renderItems();
}

async function updateMonedaItem(index, nuevaMonedaRaw) {
    const item = items[index];
    if (!item) {
        return;
    }

    const monedaActual = normalizeCurrency(item.moneda_precio || 'PEN');
    const monedaNueva = normalizeCurrency(nuevaMonedaRaw);

    if (monedaActual === monedaNueva) {
        return;
    }

    const rate = await pedirTipoCambio(monedaActual, monedaNueva, 'cambiar moneda de item');
    if (rate === null) {
        renderItems();
        return;
    }

    const precioConvertido = convertAmount(item.precio, monedaActual, monedaNueva, rate);
    if (!Number.isFinite(precioConvertido)) {
        Swal.fire('Error', 'No se pudo convertir el precio del item.', 'error');
        renderItems();
        return;
    }

    items[index].precio = Number(precioConvertido.toFixed(2));
    items[index].moneda_precio = monedaNueva;
    renderItems();
}

function removeItem(index) {
    items.splice(index, 1);
    renderItems();
}

function updateDetalle(index, value) {
    items[index].detalle_servicio = value
        .split(/\r?\n/)
        .map(line => line.trim())
        .filter(Boolean);
}

function renderItemDetails(item) {
    const tipoBadge = item.tipo === 'servicio'
        ? '<span class="badge badge-info">Servicio</span>'
        : '<span class="badge badge-success">Producto</span>';

    let html = `
        <div>${tipoBadge}</div>
        <div class="mt-1"><strong>${escapeHtml(item.nombre)}</strong></div>
    `;

    if (Array.isArray(item.detalle_servicio) && item.detalle_servicio.length) {
        html += '<ul class="cot-service-steps">';
        item.detalle_servicio.forEach(paso => {
            html += `<li>${escapeHtml(paso)}</li>`;
        });
        html += '</ul>';
    }

    return html;
}

function renderItems() {
    if (!items.length) {
        $('#itemsTable').html(`
            <tr>
                <td colspan="7" class="text-center text-muted">
                    Aun no agregaste items
                </td>
            </tr>
        `);
        calc();
        updateModoCotizacion();
        return;
    }

    let html = '';

    items.forEach((item, index) => {
        const detalleTexto = Array.isArray(item.detalle_servicio)
            ? item.detalle_servicio.join('\n')
            : '';

        html += `
            <tr>
                <td class="text-capitalize">${item.tipo}</td>
                <td class="text-left">
                    ${renderItemDetails(item)}
                    <div class="cot-detail-editor">
                        <label class="mb-1 font-weight-normal">Detalle del item</label>
                        <textarea
                            class="form-control"
                            placeholder="Una linea por trabajo o alcance. Ejemplo:&#10;Instalacion y conexion electrica&#10;Configuracion del dispensador&#10;Prueba y funcionamiento"
                            oninput="updateDetalle(${index}, this.value)">${escapeHtml(detalleTexto)}</textarea>
                        <small class="text-muted">Cada linea se mostrara debajo del item en el PDF.</small>
                    </div>
                </td>
                <td style="max-width: 110px;">
                    <input type="number"
                        min="1"
                        step="1"
                        class="form-control"
                        value="${item.cantidad}"
                        onchange="updateCantidad(${index}, this.value)">
                </td>
                <td>
                    <select class="form-control form-control-sm" onchange="updateMonedaItem(${index}, this.value)">
                        <option value="PEN" ${normalizeCurrency(item.moneda_precio || 'PEN') === 'PEN' ? 'selected' : ''}>PEN</option>
                        <option value="USD" ${normalizeCurrency(item.moneda_precio || 'PEN') === 'USD' ? 'selected' : ''}>USD</option>
                    </select>
                </td>
                <td>${formatCurrency(item.precio, item.moneda_precio)}</td>
                <td>${formatCurrency(item.precio * item.cantidad, item.moneda_precio)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="removeItem(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    $('#itemsTable').html(html);
    calc();
    updateModoCotizacion();
}

function calc() {
    const moneda = getQuoteCurrency();
    const rate = getExchangeRate();
    let conversionError = false;
    const subtotal = items.reduce((sum, item) => {
        const subItem = Number(item.precio || 0) * Number(item.cantidad || 0);
        const convertido = convertAmount(subItem, item.moneda_precio, moneda, rate);

        if (!Number.isFinite(convertido)) {
            conversionError = true;
            return sum;
        }

        return sum + convertido;
    }, 0);
    const incluyeIgv = incluyeIgvSeleccionado();
    const igv = incluyeIgv ? (subtotal * 0.18) : 0;
    const total = subtotal + igv;

    if (conversionError) {
        $('#error_tipo_cambio').text('Ingresa tipo de cambio para convertir entre PEN y USD en el resumen.');
        $('#subtotal').text('-');
        $('#igv').text('-');
        $('#total').text('-');
        $('#monedaResumen').text(moneda);
        return;
    }

    if (!$('#tipo_cambio').hasClass('is-invalid')) {
        $('#error_tipo_cambio').text('');
    }

    $('#igvLabel').text(incluyeIgv ? 'IGV (18%):' : 'IGV (0%):');
    $('#monedaResumen').text(moneda);
    $('#subtotal').text(formatCurrency(Number(subtotal.toFixed(2)), moneda));
    $('#igv').text(formatCurrency(Number(igv.toFixed(2)), moneda));
    $('#total').text(formatCurrency(Number(total.toFixed(2)), moneda));
}

function requiresCurrencyConversion() {
    const monedaCotizacion = getQuoteCurrency();
    return items.some(item => normalizeCurrency(item.moneda_precio || 'PEN') !== monedaCotizacion);
}

function getSelectedMediosPago() {
    return $('.cot-medio-pago:checked')
        .map(function () {
            return $(this).val();
        })
        .get();
}

function setSelectedMediosPago(keys = null) {
    const selected = new Set(
        Array.isArray(keys)
            ? keys
            : Object.keys(mediosPagoDisponibles || {})
    );

    $('.cot-medio-pago').each(function () {
        $(this).prop('checked', selected.has($(this).val()));
    });
}

function buildPayload() {
    return {
        cliente_id: $('#cliente_id').val(),
        asunto: $('#asunto').val().trim(),
        fecha: $('#fecha').val() || null,
        moneda: getQuoteCurrency(),
        tipo_cambio: getExchangeRate(),
        descripcion_general: $('#descripcion_general').val().trim(),
        notas: $('#notas').val().trim(),
        medios_pago: getSelectedMediosPago(),
        incluye_igv: incluyeIgvSeleccionado(),
        estado: $('#estado').val(),
        items: items.map(item => ({
            tipo: item.tipo,
            cantidad: Number(item.cantidad),
            precio: Number(item.precio || 0),
            producto_id: item.tipo === 'producto' ? item.producto_id : null,
            servicio_id: item.tipo === 'servicio' ? item.servicio_id : null,
            moneda_precio: normalizeCurrency(item.moneda_precio || 'PEN'),
            detalle_servicio: Array.isArray(item.detalle_servicio) ? item.detalle_servicio : []
        }))
    };
}

async function guardarCotizacion() {
    if (cargando) {
        return;
    }

    limpiarErrores();
    syncPresetControlsFromNotes();

    if (!$('#cliente_id').val()) {
        $('#cliente_search').addClass('is-invalid');
        $('#error_cliente_id').text('Seleccione un cliente');
        return;
    }

    if (!items.length) {
        Swal.fire('Error', 'Debes agregar al menos un item', 'error');
        return;
    }

    if (requiresCurrencyConversion() && !getExchangeRate()) {
        $('#error_tipo_cambio').text('Ingresa tipo de cambio para convertir entre PEN y USD.');
        Swal.fire('Error', 'Ingresa tipo de cambio para completar la cotizacion con monedas mixtas.', 'error');
        return;
    }

    cargando = true;
    $('#btnGuardar').prop('disabled', true);

    const payload = buildPayload();
    const url = editingCotizacionId ? `/api/cotizaciones/${editingCotizacionId}` : '/api/cotizaciones';
    const method = editingCotizacionId ? 'PUT' : 'POST';

    try {
        const resp = await apiFetch(url, {
            method,
            body: JSON.stringify(payload)
        });

        await Swal.fire('OK', resp.message, 'success');
        window.location.href = '/cotizaciones';
    } catch (err) {
        if (err.errors) {
            mostrarErrores(err.errors);
        } else {
            Swal.fire('Error', err.message || 'No se pudo guardar la cotizacion', 'error');
        }
    } finally {
        cargando = false;
        $('#btnGuardar').prop('disabled', false);
    }
}

function hydrateCotizacion(cotizacion) {
    if (cotizacion.cliente) {
        $('#cliente_search').val(cotizacion.cliente.razon_social || '');
        renderClientePreview(cotizacion.cliente);
    }

    $('#asunto').val(cotizacion.asunto || '').data('auto-generated', false);
    $('#fecha').val((cotizacion.fecha || '').slice(0, 10));
    $('#tipo_cambio').val(Number(cotizacion.tipo_cambio || lastExchangeRate || 3.8).toFixed(4));
    $('#descripcion_general').val(cotizacion.descripcion_general || '');
    $('#notas').val(cotizacion.notas || '');
    const notas = (cotizacion.notas || '').split(/\r?\n/).map(line => line.trim());
    const incluyeIgv = typeof cotizacion.incluye_igv === 'boolean'
        ? cotizacion.incluye_igv
        : (notas.includes(presetKeys.igv_no) ? false : Number(cotizacion.igv || 0) > 0);
    $('#incluye_igv').val(incluyeIgv ? '1' : '0');
    setQuoteCurrency(cotizacion.moneda || 'PEN');
    setSelectedMediosPago(Array.isArray(cotizacion.medios_pago) ? cotizacion.medios_pago : null);
    $('#estado').val(cotizacion.estado || 'borrador');

    items = (cotizacion.detalles || []).map(detalle => ({
        tipo: detalle.tipo,
        producto_id: detalle.producto_id,
        servicio_id: detalle.servicio_id,
        nombre: detalle.nombre_item || '',
        precio: Number(detalle.precio || 0),
        moneda_precio: normalizeCurrency(detalle.moneda_precio || cotizacion.moneda || 'PEN'),
        cantidad: Number(detalle.cantidad || 1),
        detalle_servicio: Array.isArray(detalle.detalle_servicio) ? detalle.detalle_servicio : []
    }));

    syncPresetControlsFromNotes();
    renderItems();
}

async function loadCotizacion() {
    if (!editingCotizacionId) {
        $('#fecha').val(new Date().toISOString().slice(0, 10));
        $('#incluye_igv').val('1');
        setQuoteCurrency('PEN');
        $('#tipo_cambio').val(Number(lastExchangeRate || 3.8).toFixed(4));
        renderItems();
        syncPresetControlsFromNotes();
        return;
    }

    $('#tituloFormulario').html('<i class="fas fa-edit text-warning"></i> Editar Cotizacion');
    $('#subtituloFormulario').text(`Actualiza la cotizacion #${editingCotizacionId}.`);
    $('#btnGuardar').text('Actualizar cotizacion');
    $('#grupoEstado').show();
    $('#btnPdf').show();

    const resp = await apiFetch(`/api/cotizaciones/${editingCotizacionId}`);
    hydrateCotizacion(resp.data);
}

async function buscarItems() {
    const termino = $('#buscar').val().trim();
    const tipo = $('#tipo').val();

    if (termino.length < 2) {
        resultadoBusqueda = [];
        renderResultados();
        return;
    }

    try {
        const endpoint = tipo === 'producto' ? 'productos' : 'servicios';
        const resp = await apiFetch(`/api/${endpoint}?search=${encodeURIComponent(termino)}&per_page=20`);

        resultadoBusqueda = (resp.data || []).map(item => ({
            ...item,
            tipo,
            moneda_precio: normalizeCurrency(item.moneda_precio || 'PEN')
        }));

        renderResultados();
    } catch (err) {
        $('#resultados').html(`
            <div class="alert alert-danger mb-0">
                No se pudo realizar la busqueda.
            </div>
        `);
    }
}

async function init() {
    try {
        setQuoteCurrency($('#moneda').val() || 'PEN');
        setSelectedMediosPago(null);
        await cargarClientesRecientes();
        await loadCotizacion();
    } catch (err) {
        Swal.fire('Error', err.message || 'No se pudo cargar la informacion inicial', 'error');
    }
}

$('#tipo').on('change', function () {
    resultadoBusqueda = [];
    buscarItems();
});
$('#moneda').on('change', function () {
    setQuoteCurrency($(this).val());
    calc();
});
$('#tipo_cambio').on('input', function () {
    $(this).removeClass('is-invalid');
    calc();
});
$('#buscar').on('input', function () {
    clearTimeout(debounceItems);
    debounceItems = setTimeout(buscarItems, 250);
});
$('#cliente_search').on('focus', function () {
    if (!$(this).val().trim()) {
        renderClienteOptions(clientesRecientes, 'Clientes recientes');
    }
});
$('#cliente_search').on('input', function () {
    $('#cliente_id').val('');
    clearTimeout(debounceCliente);
    debounceCliente = setTimeout(buscarClientes, 250);
});
$('#btnGuardar').on('click', guardarCotizacion);
$('#btnGuardarClienteRapido').on('click', guardarClienteRapido);
$('#btnGuardarProductoRapido').on('click', guardarProductoRapido);
$('#btnGuardarServicioRapido').on('click', guardarServicioRapido);
$('#presetValidez').on('change', function () {
    upsertSelectNote('Validez de la oferta:', $(this).val());
});
$('#presetDuracion').on('change', function () {
    upsertSelectNote('Duracion de trabajos:', $(this).val());
});
$('#incluye_igv').on('change', syncIgvNotesWithSelector);
$('#asunto').on('input', function () {
    $(this).data('auto-generated', false);
});
$('#notas').on('blur', syncPresetControlsFromNotes);
$('#modalClienteRapido').on('hidden.bs.modal', function () {
    $('#quick_tipo_doc').val('1');
    $('#quick_num_doc').val('');
    $('#quick_razon_social').val('');
    $('#quick_telefono').val('');
    $('#quick_email').val('');
    $('#quick_direccion').val('');
});
$('#modalProductoRapido').on('hidden.bs.modal', function () {
    $('#quick_producto_descripcion').val('');
    $('#quick_producto_categoria').val('');
    $('#quick_producto_unidad').val('NIU');
    $('#quick_producto_precio').val('');
    $('#quick_producto_moneda_precio').val('PEN');
    $('#quick_producto_stock').val('0');
});
$('#modalServicioRapido').on('hidden.bs.modal', function () {
    $('#quick_servicio_nombre').val('');
    $('#quick_servicio_precio').val('');
    $('#quick_servicio_moneda_precio').val('PEN');
    $('#quick_servicio_descripcion').val('');
    $('#quick_servicio_tipo').val('');
    $('#quick_servicio_nivel').val('');
});

$(document).on('click', function (event) {
    if (!$(event.target).closest('.cot-client-search').length) {
        hideClienteResults();
    }
});

$(document).ready(init);
</script>
@endpush
