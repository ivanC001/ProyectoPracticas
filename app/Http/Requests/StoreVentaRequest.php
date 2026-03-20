<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules = [

            'tipo_documento' => 'required|in:01,03',
            'fecha_emision' => 'required|date',
            'moneda' => 'required|in:PEN,USD',

            // CLIENTE
            'cliente' => 'nullable|array',

            // ITEMS
            'items' => 'required|array|min:1',
            'items.*.codigo' => 'required|string|max:50',
            'items.*.descripcion' => 'required|string|max:255',
            'items.*.unidad' => 'required|string|max:5',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.valor_unitario' => 'required|numeric|min:0',
        ];

        // 🔥 VALIDACIÓN DINÁMICA
        if ($this->tipo_documento == '01') {

            // FACTURA
            $rules['cliente'] = 'required|array';
            $rules['cliente.tipo_doc'] = 'required|in:6';
            $rules['cliente.num_doc'] = 'required|digits:11';
            $rules['cliente.razon_social'] = 'required|string|max:255';

        } else {

            // BOLETA
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

            'cliente.num_doc.required' => 'El número de documento es obligatorio en factura',
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

            'items.*.descripcion.required' => 'La descripción del producto es obligatoria',
            'items.*.descripcion.max' => 'La descripción es demasiado larga',

            'items.*.unidad.required' => 'La unidad del producto es obligatoria',
            'items.*.unidad.max' => 'La unidad no es válida',

            'items.*.cantidad.required' => 'La cantidad es obligatoria',
            'items.*.cantidad.numeric' => 'La cantidad debe ser un número',
            'items.*.cantidad.min' => 'La cantidad debe ser mayor a 0',

            'items.*.valor_unitario.required' => 'El precio es obligatorio',
            'items.*.valor_unitario.numeric' => 'El precio debe ser numérico',
            'items.*.valor_unitario.min' => 'El precio no puede ser negativo',
        ];
    }
}