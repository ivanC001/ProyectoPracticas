<?php

namespace App\Http\Requests;

use App\Models\GuiasModel\GuiaRemision;
use App\Models\VentasModel\Venta;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreGuiaRemisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_documento' => 'required|in:09,31',
            'fecha_emision' => 'required|date',
            'fecha_traslado' => 'required|date',

            'motivo_traslado_codigo' => 'required|string|size:2',
            'motivo_traslado_descripcion' => 'required|string|max:255',
            'modalidad_transporte' => 'required|in:01,02',
            'peso_total' => 'required|numeric|min:0.001',
            'unidad_peso' => 'nullable|string|max:3',
            'numero_bultos' => 'nullable|integer|min:1',
            'observacion' => 'nullable|string|max:500',

            'destinatario.tipo_doc' => 'required|in:0,1,6',
            'destinatario.num_doc' => 'required|string|max:20',
            'destinatario.razon_social' => 'required|string|max:255',

            'partida.ubigeo' => 'required|digits:6',
            'partida.direccion' => 'required|string|max:255',
            'llegada.ubigeo' => 'required|digits:6',
            'llegada.direccion' => 'required|string|max:255',

            'transportista' => 'nullable|array',
            'transportista.tipo_doc' => 'nullable|in:6',
            'transportista.num_doc' => 'nullable|string|max:20',
            'transportista.razon_social' => 'nullable|string|max:255',
            'transportista.reg_mtc' => 'nullable|string|max:30',

            'conductor' => 'nullable|array',
            'conductor.tipo_doc' => 'nullable|in:1,4,7',
            'conductor.num_doc' => 'nullable|string|max:20',
            'conductor.nombres' => 'nullable|string|max:255',
            'conductor.licencia' => 'nullable|string|max:40',

            'vehiculo' => 'nullable|array',
            'vehiculo.placa' => 'nullable|string|max:20',
            'vehiculo.secundario_placa' => 'nullable|string|max:20',

            'venta_id' => 'nullable|integer|exists:ventas,id',
            'guia_remitente_id' => 'nullable|integer|exists:guias_remision,id',
            'documento_relacionado' => 'nullable|array',
            'documento_relacionado.tipo' => [
                'nullable',
                'string',
                'size:2',
                Rule::in(collect(config('sunat_guia.documentos_relacionados', []))->pluck('codigo')->all()),
            ],
            'documento_relacionado.numero' => 'nullable|string|max:50',
            'documento_relacionado.emisor' => 'nullable|string|max:20',

            'detalles' => 'nullable|array',
            'detalles.*.tipo_item' => 'nullable|in:producto,servicio',
            'detalles.*.item_id' => 'nullable|integer|min:1',
            'detalles.*.codigo' => 'nullable|string|max:50',
            'detalles.*.descripcion' => 'required|string|max:500',
            'detalles.*.unidad' => 'nullable|string|max:3',
            'detalles.*.cantidad' => 'required|numeric|min:0.001',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_documento.required' => 'Debe seleccionar tipo de guia (09 o 31).',
            'tipo_documento.in' => 'El tipo de guia no es valido.',
            'fecha_emision.required' => 'La fecha de emision es obligatoria.',
            'fecha_traslado.required' => 'La fecha de traslado es obligatoria.',
            'motivo_traslado_codigo.required' => 'Debe seleccionar el motivo de traslado.',
            'motivo_traslado_codigo.size' => 'El codigo de motivo debe tener 2 caracteres.',
            'motivo_traslado_descripcion.required' => 'Debe ingresar la descripcion del traslado.',
            'modalidad_transporte.required' => 'Debe seleccionar modalidad de transporte.',
            'modalidad_transporte.in' => 'La modalidad de transporte no es valida.',
            'peso_total.required' => 'Debe ingresar el peso total.',
            'peso_total.min' => 'El peso total debe ser mayor a 0.',
            'destinatario.tipo_doc.required' => 'Debe indicar tipo de documento del destinatario.',
            'destinatario.tipo_doc.in' => 'El tipo de documento del destinatario debe ser RUC, DNI o sin documento.',
            'destinatario.num_doc.required' => 'Debe indicar documento del destinatario.',
            'destinatario.razon_social.required' => 'Debe indicar nombre o razon social del destinatario.',
            'partida.ubigeo.required' => 'El ubigeo de partida es obligatorio.',
            'partida.ubigeo.digits' => 'El ubigeo de partida debe tener 6 digitos.',
            'partida.direccion.required' => 'La direccion de partida es obligatoria.',
            'llegada.ubigeo.required' => 'El ubigeo de llegada es obligatorio.',
            'llegada.ubigeo.digits' => 'El ubigeo de llegada debe tener 6 digitos.',
            'llegada.direccion.required' => 'La direccion de llegada es obligatoria.',
            'venta_id.exists' => 'El comprobante relacionado no existe.',
            'guia_remitente_id.exists' => 'La guia remitente relacionada no existe.',
            'detalles.*.descripcion.required' => 'Cada item debe tener descripcion.',
            'detalles.*.cantidad.required' => 'Cada item debe tener cantidad.',
            'detalles.*.cantidad.min' => 'La cantidad de cada item debe ser mayor a 0.',
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $fechaEmision = $this->input('fecha_emision');
            $fechaTraslado = $this->input('fecha_traslado');

            if ($fechaEmision && $fechaTraslado && $fechaTraslado < substr((string) $fechaEmision, 0, 10)) {
                $validator->errors()->add(
                    'fecha_traslado',
                    'La fecha de traslado no puede ser anterior a la fecha de emision.'
                );
            }

            $modalidad = (string) $this->input('modalidad_transporte');
            $tipoGuia = (string) $this->input('tipo_documento');

            $transportistaNumDoc = trim((string) data_get($this->input('transportista', []), 'num_doc', ''));
            $transportistaRazon = trim((string) data_get($this->input('transportista', []), 'razon_social', ''));
            $transportistaMtc = trim((string) data_get($this->input('transportista', []), 'reg_mtc', ''));

            $conductorNumDoc = trim((string) data_get($this->input('conductor', []), 'num_doc', ''));
            $conductorTipoDoc = (string) data_get($this->input('conductor', []), 'tipo_doc', '');
            $conductorNombres = trim((string) data_get($this->input('conductor', []), 'nombres', ''));
            $conductorLicencia = trim((string) data_get($this->input('conductor', []), 'licencia', ''));
            $placa = trim((string) data_get($this->input('vehiculo', []), 'placa', ''));

            if ($modalidad === '01') {
                if ($transportistaNumDoc === '' || strlen(preg_replace('/\D+/', '', $transportistaNumDoc) ?? '') !== 11) {
                    $validator->errors()->add('transportista.num_doc', 'En transporte publico debe registrar RUC valido del transportista.');
                }

                if ($transportistaRazon === '') {
                    $validator->errors()->add('transportista.razon_social', 'En transporte publico debe registrar razon social del transportista.');
                }

                if ($transportistaMtc === '') {
                    $validator->errors()->add('transportista.reg_mtc', 'En transporte publico debe registrar numero MTC del transportista.');
                }
            }

            if ($modalidad === '02') {
                if ($conductorNumDoc === '') {
                    $validator->errors()->add('conductor.num_doc', 'En transporte privado debe registrar documento del conductor.');
                }

                if ($conductorNombres === '') {
                    $validator->errors()->add('conductor.nombres', 'En transporte privado debe registrar nombre del conductor.');
                }

                if ($conductorLicencia === '') {
                    $validator->errors()->add('conductor.licencia', 'En transporte privado debe registrar licencia del conductor.');
                }

                if ($placa === '') {
                    $validator->errors()->add('vehiculo.placa', 'En transporte privado debe registrar placa principal.');
                }
            }

            if ($tipoGuia === '31' && $transportistaNumDoc === '') {
                $validator->errors()->add('transportista.num_doc', 'Para guia transportista (31) debe indicar datos del transportista.');
            }

            if ($tipoGuia === '31' && $modalidad !== '01') {
                $validator->errors()->add('modalidad_transporte', 'La guia transportista (31) debe usar modalidad publica (01).');
            }

            $destinatarioTipo = (string) data_get($this->input('destinatario', []), 'tipo_doc', '');
            $destinatarioDoc = trim((string) data_get($this->input('destinatario', []), 'num_doc', ''));
            $destinatarioDocDigits = preg_replace('/\D+/', '', $destinatarioDoc) ?? '';

            if ($destinatarioTipo === '1' && strlen($destinatarioDocDigits) !== 8) {
                $validator->errors()->add('destinatario.num_doc', 'Si el destinatario usa DNI, debe tener 8 digitos.');
            }

            if ($destinatarioTipo === '6' && strlen($destinatarioDocDigits) !== 11) {
                $validator->errors()->add('destinatario.num_doc', 'Si el destinatario usa RUC, debe tener 11 digitos.');
            }

            if ($destinatarioTipo === '0' && $destinatarioDoc === '') {
                $validator->errors()->add('destinatario.num_doc', 'Si usa "sin documento", igual debe registrar un identificador interno.');
            }

            if ($transportistaNumDoc !== '' && strlen(preg_replace('/\D+/', '', $transportistaNumDoc) ?? '') !== 11) {
                $validator->errors()->add('transportista.num_doc', 'El RUC del transportista debe tener 11 digitos.');
            }

            if ($conductorTipoDoc === '1' && $conductorNumDoc !== '' && strlen(preg_replace('/\D+/', '', $conductorNumDoc) ?? '') !== 8) {
                $validator->errors()->add('conductor.num_doc', 'Si el conductor usa DNI, debe tener 8 digitos.');
            }

            if (in_array($conductorTipoDoc, ['4', '7'], true) && $conductorNumDoc !== '' && strlen($conductorNumDoc) < 6) {
                $validator->errors()->add('conductor.num_doc', 'El documento del conductor debe tener al menos 6 caracteres.');
            }

            $detalles = collect((array) $this->input('detalles', []))
                ->filter(fn ($item) => trim((string) data_get($item, 'descripcion', '')) !== '');
            $ventaId = (int) $this->input('venta_id', 0);
            $guiaRemitenteId = (int) $this->input('guia_remitente_id', 0);
            $documentoRelTipo = trim((string) data_get($this->input('documento_relacionado', []), 'tipo', ''));
            $documentoRelNumero = trim((string) data_get($this->input('documento_relacionado', []), 'numero', ''));
            $documentoRelEmisor = preg_replace('/\D+/', '', trim((string) data_get($this->input('documento_relacionado', []), 'emisor', ''))) ?? '';

            if ($detalles->isEmpty() && $ventaId <= 0 && $guiaRemitenteId <= 0) {
                $validator->errors()->add('detalles', 'Debe agregar al menos un item o seleccionar un comprobante/guia relacionada.');
            }

            if ($ventaId > 0 && $guiaRemitenteId > 0) {
                $validator->errors()->add(
                    'guia_remitente_id',
                    'Solo puedes relacionar una factura/boleta o una guia remitente, no ambas al mismo tiempo.'
                );
            }

            if (($documentoRelTipo !== '' && $documentoRelNumero === '') || ($documentoRelTipo === '' && $documentoRelNumero !== '')) {
                $validator->errors()->add(
                    'documento_relacionado.numero',
                    'Si ingresas documento relacionado manual, debes completar tipo y numero.'
                );
            }

            if ($documentoRelEmisor !== '' && strlen($documentoRelEmisor) !== 11) {
                $validator->errors()->add(
                    'documento_relacionado.emisor',
                    'El RUC emisor del documento relacionado debe tener 11 digitos.'
                );
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $catalogMotivos = collect(config('sunat_guia.motivos_traslado', []))
                ->pluck('codigo')
                ->filter()
                ->values()
                ->all();

            $codigoMotivo = (string) $this->input('motivo_traslado_codigo');

            if (!empty($catalogMotivos) && !in_array($codigoMotivo, $catalogMotivos, true)) {
                $validator->errors()->add('motivo_traslado_codigo', 'El motivo de traslado no pertenece al catalogo configurado.');
            }

            if ($ventaId > 0) {
                $venta = Venta::query()->find($ventaId);

                if (!$venta) {
                    $validator->errors()->add('venta_id', 'El comprobante relacionado no existe.');
                    return;
                }

                if ($venta->estado_envio !== 'aceptado') {
                    $validator->errors()->add('venta_id', 'Solo puedes relacionar comprobantes aceptados por SUNAT.');
                }

                if (!in_array((string) $venta->tipo_documento, ['01', '03'], true)) {
                    $validator->errors()->add('venta_id', 'El comprobante relacionado debe ser factura o boleta.');
                }
            }

            if ($guiaRemitenteId > 0) {
                $guiaRemitente = GuiaRemision::query()->find($guiaRemitenteId);

                if (!$guiaRemitente) {
                    $validator->errors()->add('guia_remitente_id', 'La guia remitente relacionada no existe.');
                } else {
                    if ((string) $guiaRemitente->tipo_documento !== '09') {
                        $validator->errors()->add('guia_remitente_id', 'Solo se puede relacionar una guia de tipo 09 (remitente).');
                    }
                }
            }

            if ($tipoGuia === '31' && $ventaId <= 0 && $guiaRemitenteId <= 0 && ($documentoRelNumero === '' || $documentoRelTipo === '')) {
                $validator->errors()->add(
                    'documento_relacionado.numero',
                    'Para guia transportista debes relacionar una factura/boleta, una guia remitente o ingresar tipo y numero del documento de referencia.'
                );
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validacion',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
