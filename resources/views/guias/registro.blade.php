@php
    $tiposGuia = config('sunat_guia.tipos_documento_guia', []);
    $motivos = config('sunat_guia.motivos_traslado', []);
    $modalidades = config('sunat_guia.modalidades_transporte', []);
@endphp

<form id="formGuiaRemision">
    @csrf
    <input type="hidden" id="guia_id">
    <div id="guiaValidationBox" class="alert alert-danger d-none"></div>

    <div class="row">
        <div class="col-md-3">
            <label for="gr_tipo_documento">Tipo guia</label>
            <select id="gr_tipo_documento" class="form-control">
                @foreach($tiposGuia as $tipo)
                    <option value="{{ $tipo['codigo'] }}">{{ $tipo['codigo'] }} - {{ $tipo['descripcion'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="gr_fecha_emision">Fecha emision</label>
            <input type="datetime-local" id="gr_fecha_emision" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="gr_fecha_traslado">Fecha traslado</label>
            <input type="date" id="gr_fecha_traslado" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="gr_factura_search">Factura relacionada (opcional)</label>
            <div class="position-relative">
                <input type="hidden" id="gr_venta_id">
                <input type="text" id="gr_factura_search" class="form-control" placeholder="Buscar por comprobante o cliente...">
                <div id="gr_factura_results" class="list-group position-absolute w-100 d-none" style="z-index: 1060; max-height: 230px; overflow-y: auto;"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <small id="gr_factura_selected" class="text-muted">Sin factura relacionada</small>
                <button type="button" class="btn btn-link btn-sm p-0" onclick="limpiarFacturaRelacionada()">Limpiar</button>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-3">
            <label for="gr_motivo_codigo">Motivo traslado</label>
            <select id="gr_motivo_codigo" class="form-control">
                @foreach($motivos as $motivo)
                    <option value="{{ $motivo['codigo'] }}">{{ $motivo['codigo'] }} - {{ $motivo['descripcion'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label for="gr_motivo_descripcion">Descripcion motivo</label>
            <input type="text" id="gr_motivo_descripcion" class="form-control" maxlength="255">
        </div>
        <div class="col-md-2">
            <label for="gr_modalidad">Modalidad</label>
            <select id="gr_modalidad" class="form-control">
                @foreach($modalidades as $modalidad)
                    <option value="{{ $modalidad['codigo'] }}">{{ $modalidad['descripcion'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="gr_peso_total">Peso total</label>
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
    <div class="row">
        <div class="col-md-2">
            <label for="gr_dest_tipo_doc">Tipo doc</label>
            <select id="gr_dest_tipo_doc" class="form-control">
                <option value="6">RUC</option>
                <option value="1">DNI</option>
                <option value="4">CE</option>
                <option value="7">Pasaporte</option>
                <option value="0">Sin doc</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="gr_dest_num_doc">Numero</label>
            <input type="text" id="gr_dest_num_doc" class="form-control">
        </div>
        <div class="col-md-7">
            <label for="gr_dest_razon_social">Nombre / Razon social</label>
            <input type="text" id="gr_dest_razon_social" class="form-control">
        </div>
    </div>

    <hr>
    <h6><i class="fas fa-map-marker-alt"></i> Direcciones</h6>
    <div class="row">
        <div class="col-md-3">
            <label for="gr_partida_ubigeo">Partida ubigeo</label>
            <input type="text" id="gr_partida_ubigeo" class="form-control" maxlength="6">
        </div>
        <div class="col-md-9">
            <label for="gr_partida_direccion">Partida direccion</label>
            <input type="text" id="gr_partida_direccion" class="form-control">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-3">
            <label for="gr_llegada_ubigeo">Llegada ubigeo</label>
            <input type="text" id="gr_llegada_ubigeo" class="form-control" maxlength="6">
        </div>
        <div class="col-md-9">
            <label for="gr_llegada_direccion">Llegada direccion</label>
            <input type="text" id="gr_llegada_direccion" class="form-control">
        </div>
    </div>

    <hr>
    <h6><i class="fas fa-truck"></i> Transporte</h6>
    <small class="text-muted d-block mb-2">
        Publico: datos de empresa transportista. Privado: datos de conductor y vehiculo.
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
                <label for="gr_trans_num_doc">RUC</label>
                <input type="text" id="gr_trans_num_doc" class="form-control">
            </div>
            <div class="col-md-5">
                <label for="gr_trans_razon_social">Razon social</label>
                <input type="text" id="gr_trans_razon_social" class="form-control">
            </div>
            <div class="col-md-2">
                <label for="gr_trans_reg_mtc">Reg. MTC</label>
                <input type="text" id="gr_trans_reg_mtc" class="form-control">
            </div>
        </div>
    </div>

    <div id="bloquePrivado" class="border rounded p-2 mb-2">
        <h6 class="text-primary mb-2">Conductor y vehiculo</h6>
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
                <label for="gr_cond_num_doc">Documento</label>
                <input type="text" id="gr_cond_num_doc" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="gr_cond_nombres">Nombres completos</label>
                <input type="text" id="gr_cond_nombres" class="form-control">
            </div>
            <div class="col-md-2">
                <label for="gr_cond_licencia">Licencia</label>
                <input type="text" id="gr_cond_licencia" class="form-control">
            </div>
            <div class="col-md-2">
                <label for="gr_veh_placa">Placa principal</label>
                <input type="text" id="gr_veh_placa" class="form-control">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2">
                <label for="gr_veh_sec_placa">Placa secundaria</label>
                <input type="text" id="gr_veh_sec_placa" class="form-control">
            </div>
        </div>
    </div>

    <hr>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0"><i class="fas fa-list"></i> Detalle de traslado</h6>
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
