<div class="modal fade" id="modalRegistroCliente">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 id="tituloModal">Registrar Cliente</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <form id="formCliente">

                    <select class="form-control mb-2" id="tipo_doc">
                        <option value="1">DNI</option>
                        <option value="6">RUC</option>
                    </select>

                    <input type="text" class="form-control mb-2" id="num_doc" placeholder="Documento">
                    <input type="text" class="form-control mb-2" id="razon_social" placeholder="Nombre">
                    <input type="text" class="form-control mb-2" id="telefono" placeholder="Teléfono">
                    <input type="email" class="form-control mb-2" id="email" placeholder="Email">
                    <input type="text" class="form-control mb-2" id="direccion" placeholder="Dirección">

                </form>

                <!-- 🔥 MENSAJE -->
                <div id="respuestaServidor" class="alert d-none mt-2"></div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="btnGuardar" onclick="guardarCliente()">
                    Guardar
                </button>
                <button class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
            </div>

        </div>
    </div>
</div>


@push('scripts')
<script>

/* 🔥 VARIABLE GLOBAL */
window.clienteEditando = null;

/* 🔥 LIMPIAR MODAL */
$('#modalRegistroCliente').on('show.bs.modal', function () {

    if(!window.clienteEditando){
        $('#formCliente')[0].reset();
        $('#tituloModal').text('Registrar Cliente');
    }

    $('#respuestaServidor').addClass('d-none').text('');
});

/* 🔥 MOSTRAR MENSAJE */
function mostrarRespuesta(msg, tipo='success'){
    let div = $('#respuestaServidor');

    div.removeClass('d-none alert-success alert-danger');
    div.addClass(`alert alert-${tipo}`);
    div.text(msg);
}

/* 🔥 EDITAR (SE USA DESDE INDEX) */
function editar(id){

    fetch(`/api/clientes/${id}`)
    .then(r=>r.json())
    .then(c => {

        $('#tipo_doc').val(c.tipo_doc);
        $('#num_doc').val(c.num_doc);
        $('#razon_social').val(c.razon_social);
        $('#telefono').val(c.telefono);
        $('#email').val(c.email);
        $('#direccion').val(c.direccion);

        window.clienteEditando = id;

        $('#tituloModal').text('Editar Cliente');

        $('#modalRegistroCliente').modal('show');

    });

}

/* 🔥 GUARDAR / ACTUALIZAR */
function guardarCliente(){

    let btn = $('#btnGuardar');
    btn.prop('disabled', true).text('Guardando...');

    let data = {
        tipo_doc: $('#tipo_doc').val(),
        num_doc: $('#num_doc').val(),
        razon_social: $('#razon_social').val(),
        telefono: $('#telefono').val(),
        email: $('#email').val(),
        direccion: $('#direccion').val(),
        estado: true
    };

    let url = '/api/clientes';
    let method = 'POST';

    // 🔥 SI ES EDITAR
    if(window.clienteEditando){
        url = `/api/clientes/${window.clienteEditando}`;
        method = 'PUT';
    }

    fetch(url,{
        method: method,
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify(data)
    })
    .then(async r => {

        let resp = await r.json();

        if(!r.ok){
            throw resp;
        }

        return resp;
    })
    .then(resp => {

        // 🔥 MOSTRAR RESPUESTA API
        mostrarRespuesta(resp.message || 'Operación exitosa');

        setTimeout(()=>{
            $('#modalRegistroCliente').modal('hide');
            window.clienteEditando = null;
        }, 1500);

    })
    .catch(err => {

        if(err.errors){
            let errores = Object.values(err.errors).flat().join('\n');
            mostrarRespuesta(errores, 'danger');
        }else{
            mostrarRespuesta(err.message || 'Error', 'danger');
        }

    })
    .finally(()=>{
        btn.prop('disabled', false).text('Guardar');
    });

}

</script>
@endpush