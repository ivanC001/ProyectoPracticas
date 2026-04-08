<form id="formNota">

@csrf

<div class="row">

    <div class="col-md-4">
        <label>Tipo Nota</label>
        <select class="form-control" id="tipo_documento_nota">
            <option value="07">Nota Crédito</option>
            <option value="08">Nota Débito</option>
        </select>
    </div>

    <div class="col-md-4">
        <label>ID Venta</label>
        <input type="number" class="form-control" id="venta_id">
    </div>

</div>

<hr>

<div class="row">

    <div class="col-md-4">
        <label>Motivo</label>
        <select class="form-control" id="codMotivo">
            <option value="01">Anulación</option>
            <option value="02">Error en datos</option>
            <option value="07">Devolución</option>
        </select>
    </div>

    <div class="col-md-8">
        <label>Descripción</label>
        <input type="text" class="form-control" id="desMotivo">
    </div>

</div>

</form>

@push('scripts')
<script>

async function procesarNota(){

    let data = {
        venta_id: document.getElementById('venta_id').value,
        tipo_documento: document.getElementById('tipo_documento_nota').value,
        codMotivo: document.getElementById('codMotivo').value,
        desMotivo: document.getElementById('desMotivo').value
    };

    try{

        let resp = await apiFetch('/api/facturacion/notas',{
            method:'POST',
            body: JSON.stringify(data)
        });

        Swal.fire('OK','Nota enviada','success');

        $('#modalNota').modal('hide');

    }catch(err){
        Swal.fire('Error', err.message || 'Error','error');
    }

}

</script>
@endpush