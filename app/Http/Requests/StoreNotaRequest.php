<?php

namespace App\Http\Requests;

use App\Models\VentasModel\Venta;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'venta_id' => 'required|integer|exists:ventas,id',
            'tipo_documento' => 'required|in:07,08',
            'codMotivo' => 'required|string|size:2',
            'desMotivo' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'venta_id.required' => 'Debe seleccionar una factura emitida.',
            'venta_id.integer' => 'La factura seleccionada no es valida.',
            'venta_id.exists' => 'La factura seleccionada no existe.',
            'tipo_documento.required' => 'Debe seleccionar el tipo de nota.',
            'tipo_documento.in' => 'El tipo de nota no es valido.',
            'codMotivo.required' => 'Debe seleccionar el motivo de la nota.',
            'codMotivo.size' => 'El codigo de motivo debe tener 2 caracteres.',
            'desMotivo.required' => 'Debe ingresar la descripcion del motivo.',
            'desMotivo.max' => 'La descripcion del motivo es demasiado larga.',
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $tipoNota = (string) $this->input('tipo_documento');
            $codMotivo = (string) $this->input('codMotivo');

            $motivosCredito = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13'];
            $motivosDebito = ['01', '02', '03'];

            if ($tipoNota === '07' && !in_array($codMotivo, $motivosCredito, true)) {
                $validator->errors()->add('codMotivo', 'El motivo seleccionado no corresponde a Nota de Credito.');
            }

            if ($tipoNota === '08' && !in_array($codMotivo, $motivosDebito, true)) {
                $validator->errors()->add('codMotivo', 'El motivo seleccionado no corresponde a Nota de Debito.');
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $venta = Venta::query()->find((int) $this->input('venta_id'));

            if (!$venta) {
                $validator->errors()->add('venta_id', 'La factura seleccionada no existe.');
                return;
            }

            if ($venta->estado_envio !== 'aceptado') {
                $validator->errors()->add('venta_id', 'Solo se puede generar notas sobre facturas aceptadas por SUNAT.');
            }

            if (!in_array((string) $venta->tipo_documento, ['01', '03'], true)) {
                $validator->errors()->add('venta_id', 'El comprobante seleccionado no admite nota de credito/debito.');
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
