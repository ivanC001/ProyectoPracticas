<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [

            // 🔥 GENERALES
            'tipo_documento' => 'required|in:01,03',
            'fecha_emision' => 'required|date',
            'moneda' => 'required|in:PEN,USD',

            // 🔥 CLIENTE
            'cliente' => 'nullable|array',

            // 🔥 ITEMS (CORREGIDO)
            'items' => 'required|array|min:1',
            'items.*.codigo' => 'required|string|max:50',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.descuento' => 'nullable|numeric|min:0',
        ];

        // 🔥 VALIDACIÓN DINÁMICA
        if ($this->tipo_documento == '01') {

            // FACTURA → OBLIGATORIO
            $rules['cliente'] = 'required|array';
            $rules['cliente.tipo_doc'] = 'required|in:6';
            $rules['cliente.num_doc'] = 'required|digits:11';
            $rules['cliente.razon_social'] = 'required|string|max:255';

        } else {

            // BOLETA → OPCIONAL
            $rules['cliente.tipo_doc'] = 'nullable|in:0,1,6';
            $rules['cliente.num_doc'] = 'nullable|string|max:15';
            $rules['cliente.razon_social'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public function messages()
    {
        return [

            // 🔥 GENERALES
            'tipo_documento.required' => 'Debe seleccionar el tipo de comprobante',
            'tipo_documento.in' => 'El tipo de comprobante no es válido',

            'fecha_emision.required' => 'La fecha de emisión es obligatoria',
            'fecha_emision.date' => 'La fecha de emisión no tiene un formato válido',

            'moneda.required' => 'Debe seleccionar la moneda',
            'moneda.in' => 'La moneda no es válida',

            // 🔥 CLIENTE
            'cliente.required' => 'El cliente es obligatorio para facturas',
            'cliente.array' => 'El formato del cliente no es válido',

            'cliente.tipo_doc.required' => 'El tipo de documento es obligatorio en factura',
            'cliente.tipo_doc.in' => 'El tipo de documento no es válido',

            'cliente.num_doc.required' => 'El número de documento es obligatorio',
            'cliente.num_doc.digits' => 'El RUC debe tener exactamente 11 dígitos',
            'cliente.num_doc.max' => 'El número de documento es demasiado largo',

            'cliente.razon_social.required' => 'La razón social es obligatoria',
            'cliente.razon_social.max' => 'La razón social es demasiado larga',

            // 🔥 ITEMS
            'items.required' => 'Debe agregar al menos un producto',
            'items.array' => 'Los productos deben enviarse en formato lista',
            'items.min' => 'Debe agregar al menos un producto',

            'items.*.codigo.required' => 'El código del producto es obligatorio',
            'items.*.codigo.max' => 'El código del producto es demasiado largo',

            'items.*.cantidad.required' => 'La cantidad es obligatoria',
            'items.*.cantidad.numeric' => 'La cantidad debe ser un número',
            'items.*.cantidad.min' => 'La cantidad debe ser mayor a 0',

            'items.*.descuento.numeric' => 'El descuento debe ser numérico',
            'items.*.descuento.min' => 'El descuento no puede ser negativo',
        ];
    }

    /**
     * 🔥 RESPUESTA JSON (CLAVE PARA API)
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}