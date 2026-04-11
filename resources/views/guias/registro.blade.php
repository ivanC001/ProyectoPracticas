@php
    $tiposGuia = config('sunat_guia.tipos_documento_guia', []);
    $motivos = config('sunat_guia.motivos_traslado', []);
    $modalidades = config('sunat_guia.modalidades_transporte', []);
    $documentosRelacionados = config('sunat_guia.documentos_relacionados', []);
@endphp

<form id="formGuiaRemision">
    @csrf
    <input type="hidden" id="guia_id">
    <div id="guiaValidationBox" class="alert alert-danger d-none"></div>
    <div class="alert alert-info py-2 mb-2">
        <strong>Campos obligatorios SUNAT:</strong> tipo de guia, fecha emision, fecha traslado, motivo, modalidad,
        peso total, destinatario, partida, llegada y al menos 1 item.
    </div>
    <div class="alert alert-light border py-2 mb-3">
        <strong>Ayuda rapida:</strong> la partida se puede jalar desde la empresa, el destinatario desde clientes y,
        si relacionas un comprobante o una guia remitente, el detalle se completa automaticamente.
    </div>

    <div class="row">
        <div class="col-md-3">
            <label for="gr_tipo_documento">Tipo guia <span class="text-danger">*</span></label>
            <select id="gr_tipo_documento" class="form-control">
                @foreach($tiposGuia as $tipo)
                    <option value="{{ $tipo['codigo'] }}">{{ $tipo['codigo'] }} - {{ $tipo['descripcion'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="gr_fecha_emision">Fecha emision <span class="text-danger">*</span></label>
            <input type="datetime-local" id="gr_fecha_emision" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="gr_fecha_traslado">Fecha traslado <span class="text-danger">*</span></label>
            <input type="date" id="gr_fecha_traslado" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="gr_factura_search">Comprobante relacionado (opcional)</label>
            <div class="position-relative">
                <input type="hidden" id="gr_venta_id">
                <input type="text" id="gr_factura_search" class="form-control" placeholder="Buscar factura/boleta por numero o cliente...">
                <div id="gr_factura_results" class="list-group position-absolute w-100 d-none" style="z-index: 1060; max-height: 230px; overflow-y: auto;"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <small id="gr_factura_selected" class="text-muted">Sin comprobante relacionado</small>
                <button type="button" class="btn btn-link btn-sm p-0" onclick="limpiarFacturaRelacionada()">Limpiar</button>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-4">
            <label for="gr_remitente_search">Guia remitente relacionada (opcional)</label>
            <div class="position-relative">
                <input type="hidden" id="gr_guia_remitente_id">
                <input type="text" id="gr_remitente_search" class="form-control" placeholder="Buscar guia 09 por numero o destinatario...">
                <div id="gr_remitente_results" class="list-group position-absolute w-100 d-none" style="z-index: 1060; max-height: 230px; overflow-y: auto;"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <small id="gr_remitente_selected" class="text-muted">Sin guia remitente relacionada</small>
                <button type="button" class="btn btn-link btn-sm p-0" onclick="limpiarRemitenteRelacionado()">Limpiar</button>
            </div>
        </div>
        <div class="col-md-2">
            <label for="gr_doc_rel_tipo">Tipo doc manual</label>
            <select id="gr_doc_rel_tipo" class="form-control">
                <option value="">Seleccione</option>
                @foreach($documentosRelacionados as $docRel)
                    <option value="{{ $docRel['codigo'] }}">{{ $docRel['codigo'] }} - {{ $docRel['descripcion'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="gr_doc_rel_numero">Numero documento manual</label>
            <input type="text" id="gr_doc_rel_numero" class="form-control" placeholder="Ej: T001-00001234">
        </div>
        <div class="col-md-3">
            <label for="gr_doc_rel_emisor">RUC emisor doc. relacionado</label>
            <input type="text" id="gr_doc_rel_emisor" class="form-control" placeholder="11 digitos (se completa con tu empresa por defecto)">
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-3">
            <label for="gr_motivo_codigo">Motivo traslado <span class="text-danger">*</span></label>
            <select id="gr_motivo_codigo" class="form-control">
                @foreach($motivos as $motivo)
                    <option value="{{ $motivo['codigo'] }}">{{ $motivo['codigo'] }} - {{ $motivo['descripcion'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label for="gr_motivo_descripcion">Descripcion motivo <span class="text-danger">*</span></label>
            <input type="text" id="gr_motivo_descripcion" class="form-control" maxlength="255">
        </div>
        <div class="col-md-2">
            <label for="gr_modalidad">Modalidad <span class="text-danger">*</span></label>
            <select id="gr_modalidad" class="form-control">
                @foreach($modalidades as $modalidad)
                    <option value="{{ $modalidad['codigo'] }}">{{ $modalidad['descripcion'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="gr_peso_total">Peso total <span class="text-danger">*</span></label>
            <input type="number" min="0.001" step="0.001" id="gr_peso_total" class="form-control" value="1.000">
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-2">
            <label for="gr_unidad_peso">Unidad peso</label>
            <input type="text" id="gr_unidad_peso" class="form-control" value="KGM">
        </div>
        <div class="col-md-2">
            <label for="gr_numero_bultos">Nro bultos</label>
            <input type="number" min="1" id="gr_numero_bultos" class="form-control">
        </div>
        <div class="col-md-8">
            <label for="gr_observacion">Observacion</label>
            <input type="text" id="gr_observacion" class="form-control" maxlength="500" placeholder="Opcional">
        </div>
    </div>

    <hr>
    <h6><i class="fas fa-user"></i> Destinatario</h6>
    <div class="row mb-2">
        <div class="col-md-12">
            <div class="position-relative">
                <label for="gr_cliente_search">Buscar destinatario en clientes</label>
                <input type="text" id="gr_cliente_search" class="form-control" placeholder="Buscar por nombre o documento...">
                <div id="gr_cliente_results" class="list-group position-absolute w-100 d-none" style="z-index: 1060; max-height: 220px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <label for="gr_dest_tipo_doc">Tipo doc <span class="text-danger">*</span></label>
            <select id="gr_dest_tipo_doc" class="form-control">
                <option value="6">RUC</option>
                <option value="1">DNI</option>
                <option value="0">Sin doc</option>
            </select>
        </div>
            <div class="col-md-3">
                <label for="gr_dest_num_doc">Numero <span class="text-danger">*</span></label>
            <input type="text" id="gr_dest_num_doc" class="form-control" placeholder="RUC 11 digitos o DNI 8 digitos">
            </div>
        <div class="col-md-7">
            <label for="gr_dest_razon_social">Nombre / Razon social <span class="text-danger">*</span></label>
            <input type="text" id="gr_dest_razon_social" class="form-control">
        </div>
    </div>
    <div class="row mt-1">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <small id="gr_cliente_selected" class="text-muted">Sin cliente seleccionado</small>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="registrarNuevoClienteDesdeGuia()">
                <i class="fas fa-user-plus"></i> Registrar cliente nuevo
            </button>
        </div>
    </div>

    <hr>
    <h6><i class="fas fa-map-marker-alt"></i> Direcciones</h6>
    <div class="row">
        <div class="col-md-3">
            <label for="gr_partida_ubigeo">Partida ubigeo <span class="text-danger">*</span></label>
            <input type="text" id="gr_partida_ubigeo" class="form-control" maxlength="6">
        </div>
        <div class="col-md-9">
            <label for="gr_partida_direccion">Partida direccion <span class="text-danger">*</span></label>
            <input type="text" id="gr_partida_direccion" class="form-control">
        </div>
    </div>
    <div class="row mt-1">
        <div class="col-md-12 d-flex justify-content-end">
            <button type="button" class="btn btn-link btn-sm p-0" onclick="setPartidaEmpresa()">
                Usar datos de empresa
            </button>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-3">
            <label for="gr_llegada_ubigeo">Llegada ubigeo <span class="text-danger">*</span></label>
            <input type="text" id="gr_llegada_ubigeo" class="form-control" maxlength="6">
        </div>
        <div class="col-md-9">
            <label for="gr_llegada_direccion">Llegada direccion <span class="text-danger">*</span></label>
            <input type="text" id="gr_llegada_direccion" class="form-control">
        </div>
    </div>

    <hr>
    <h6><i class="fas fa-truck"></i> Transporte</h6>
    <small id="gr_transporte_help" class="text-muted d-block mb-2">
        Publico: transportista con RUC, MTC, placa y documento del conductor. Privado: conductor + licencia + placa.
    </small>

    <div id="bloqueTransportista" class="border rounded p-2 mb-2">
        <h6 class="text-primary mb-2">Transportista</h6>
        <div class="row">
            <div class="col-md-2">
                <label for="gr_trans_tipo_doc">Tipo doc</label>
                <select id="gr_trans_tipo_doc" class="form-control">
                    <option value="6">RUC</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="gr_trans_num_doc">RUC <span class="text-danger">*</span></label>
                <input type="text" id="gr_trans_num_doc" class="form-control">
            </div>
            <div class="col-md-5">
                <label for="gr_trans_razon_social">Razon social <span class="text-danger">*</span></label>
                <input type="text" id="gr_trans_razon_social" class="form-control">
            </div>
            <div class="col-md-2">
                <label for="gr_trans_reg_mtc">Reg. MTC <span class="text-danger">*</span></label>
                <input type="text" id="gr_trans_reg_mtc" class="form-control">
            </div>
        </div>
    </div>

    <div id="bloquePrivado" class="border rounded p-2 mb-2">
        <h6 id="gr_conductor_titulo" class="text-primary mb-2">Conductor y vehiculo</h6>
        <small id="gr_conductor_help" class="text-muted d-block mb-2">
            Completa los datos de la unidad que realiza el traslado.
        </small>
        <div class="row">
            <div class="col-md-2">
                <label for="gr_cond_tipo_doc">Tipo doc</label>
                <select id="gr_cond_tipo_doc" class="form-control">
                    <option value="1">DNI</option>
                    <option value="4">CE</option>
                    <option value="7">Pasaporte</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="gr_cond_num_doc">Documento <span class="text-danger">*</span></label>
                <input type="text" id="gr_cond_num_doc" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="gr_cond_nombres">Nombres completos <span class="text-danger">*</span></label>
                <input type="text" id="gr_cond_nombres" class="form-control">
            </div>
            <div class="col-md-2" id="gr_cond_licencia_wrap">
                <label for="gr_cond_licencia">Licencia <span class="text-danger">*</span></label>
                <input type="text" id="gr_cond_licencia" class="form-control">
            </div>
            <div class="col-md-2">
                <label for="gr_veh_placa">Placa principal <span class="text-danger">*</span></label>
                <input type="text" id="gr_veh_placa" class="form-control">
            </div>
        </div>
        <div class="row mt-2" id="gr_veh_sec_wrap">
            <div class="col-md-2">
                <label for="gr_veh_sec_placa">Placa secundaria</label>
                <input type="text" id="gr_veh_sec_placa" class="form-control">
            </div>
        </div>
    </div>

    <hr>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0"><i class="fas fa-list"></i> Detalle de traslado <span class="text-danger">*</span></h6>
        <button type="button" class="btn btn-sm btn-success" onclick="agregarFilaDetalleGuia()">
            <i class="fas fa-plus"></i> Agregar item
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="width: 12%">Tipo</th>
                    <th style="width: 14%">Codigo</th>
                    <th>Descripcion</th>
                    <th style="width: 12%">Unidad</th>
                    <th style="width: 12%">Cantidad</th>
                    <th style="width: 8%"></th>
                </tr>
            </thead>
            <tbody id="gr_detalles_body"></tbody>
        </table>
    </div>
</form>
