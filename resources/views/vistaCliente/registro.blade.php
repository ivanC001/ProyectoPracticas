<div class="modal fade" id="modalRegistroCliente">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5>Registrar Cliente</h5>
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

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" onclick="guardarCliente()">Guardar</button>
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>

        </div>
    </div>
</div>


@push('scripts')
<script>

/* 🔥 SOLO REGISTRO */
function guardarCliente(){

    let data = {
        tipo_doc: $('#tipo_doc').val(),
        num_doc: $('#num_doc').val(),
        razon_social: $('#razon_social').val(),
        telefono: $('#telefono').val(),
        email: $('#email').val(),
        direccion: $('#direccion').val(),
        estado: true
    };

    fetch('/api/clientes',{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify(data)
    })
    .then(r=>r.json())
    .then(resp=>{

        Swal.fire('OK', resp.message, 'success');

        // 👉 SOLO cerrar modal (NO tocar tabla)
        $('#modalRegistroCliente').modal('hide');

    })
    .catch(()=>{
        Swal.fire('Error','No se pudo guardar','error');
    });

}

</script>
@endpush