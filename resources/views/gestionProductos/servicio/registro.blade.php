<div class="modal fade" id="modalServicio">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 id="tituloModal">
                    <i class="fas fa-plus"></i> Registrar Servicio
                </h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <form id="formServicio">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre">
                        </div>

                        <div class="col-md-12 mt-2">
                            <label>Descripcion <small class="text-muted">(Opcional)</small></label>
                            <textarea class="form-control" id="descripcion"></textarea>
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>Precio <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="precio">
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>Moneda <span class="text-danger">*</span></label>
                            <select class="form-control" id="moneda_precio">
                                <option value="PEN">Soles (PEN)</option>
                                <option value="USD">Dolares (USD)</option>
                            </select>
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>Costo <small class="text-muted">(Opcional)</small></label>
                            <input type="number" step="0.01" class="form-control" id="costo">
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>Duracion (min) <small class="text-muted">(Opcional)</small></label>
                            <input type="number" class="form-control" id="duracion_estimada">
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Requiere personal?</label>
                            <select class="form-control" id="requiere_personal">
                                <option value="0">No</option>
                                <option value="1">Si</option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Cantidad personal</label>
                            <input type="number" class="form-control" id="cantidad_personal">
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Requiere equipo?</label>
                            <select class="form-control" id="requiere_equipo">
                                <option value="0">No</option>
                                <option value="1">Si</option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Equipos</label>
                            <input type="text" class="form-control" id="equipos_descripcion">
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Tipo de servicio</label>
                            <select class="form-control" id="tipo_servicio">
                                <option value="local">Local</option>
                                <option value="domicilio">Domicilio</option>
                                <option value="remoto">Remoto</option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Requiere transporte?</label>
                            <select class="form-control" id="requiere_transporte">
                                <option value="0">No</option>
                                <option value="1">Si</option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Garantia (dias)</label>
                            <input type="number" class="form-control" id="garantia_dias">
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Nivel de servicio</label>
                            <select class="form-control" id="nivel_servicio">
                                <option value="basico">Basico</option>
                                <option value="estandar">Estandar</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Prioridad</label>
                            <select class="form-control" id="prioridad">
                                <option value="baja">Baja</option>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Frecuencia</label>
                            <select class="form-control" id="frecuencia">
                                <option value="unico">Unico</option>
                                <option value="recurrente">Recurrente</option>
                            </select>
                        </div>

                        <div class="col-md-12 mt-2">
                            <label>Condiciones</label>
                            <textarea class="form-control" id="condiciones"></textarea>
                        </div>

                        <div class="col-md-12 mt-2">
                            <label>Requisitos del cliente</label>
                            <textarea class="form-control" id="requisitos_cliente"></textarea>
                        </div>

                        <div class="col-md-12 mt-2">
                            <label>Instrucciones</label>
                            <textarea class="form-control" id="instrucciones"></textarea>
                        </div>
                    </div>

                    <small class="text-muted mt-3 d-block">
                        <span class="text-danger">*</span> Campos obligatorios
                    </small>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="btnGuardar">Guardar</button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
window.servicioEditando = null;

function limpiarErrores(){
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

function mostrarErrores(errors){
    limpiarErrores();

    Object.keys(errors).forEach(campo => {
        let input = $(`#${campo}`);
        input.addClass('is-invalid');
        input.after(`
            <div class="invalid-feedback">
                ${errors[campo][0]}
            </div>
        `);
    });
}

$('#modalServicio').on('show.bs.modal', function () {
    if(!window.servicioEditando){
        $('#formServicio')[0].reset();
        $('#moneda_precio').val('PEN');
        $('#tituloModal').html('<i class="fas fa-plus"></i> Registrar Servicio');
    }

    limpiarErrores();
});

$('#modalServicio').on('hidden.bs.modal', function () {
    window.servicioEditando = null;
});

$('#btnGuardar').click(function(){
    limpiarErrores();

    let data = {
        nombre: $('#nombre').val(),
        descripcion: $('#descripcion').val(),
        precio: $('#precio').val(),
        moneda_precio: $('#moneda_precio').val(),
        costo: $('#costo').val(),
        duracion_estimada: $('#duracion_estimada').val(),
        requiere_personal: $('#requiere_personal').val(),
        cantidad_personal: $('#cantidad_personal').val(),
        requiere_equipo: $('#requiere_equipo').val(),
        equipos_descripcion: $('#equipos_descripcion').val(),
        tipo_servicio: $('#tipo_servicio').val(),
        requiere_transporte: $('#requiere_transporte').val(),
        garantia_dias: $('#garantia_dias').val(),
        nivel_servicio: $('#nivel_servicio').val(),
        prioridad: $('#prioridad').val(),
        frecuencia: $('#frecuencia').val(),
        condiciones: $('#condiciones').val(),
        requisitos_cliente: $('#requisitos_cliente').val(),
        instrucciones: $('#instrucciones').val()
    };

    let url = '/api/servicios';
    let method = 'POST';

    if(window.servicioEditando){
        url = `/api/servicios/${window.servicioEditando}`;
        method = 'PUT';
    }

    apiFetch(url,{
        method: method,
        body: JSON.stringify(data)
    })
    .then(resp => {
        Swal.fire('OK', resp.message, 'success');
        $('#modalServicio').modal('hide');
        fetchServicios();
    })
    .catch(err => {
        if(err.errors){
            mostrarErrores(err.errors);
        }else{
            Swal.fire('Error', err.message, 'error');
        }
    });
});
</script>
@endpush
