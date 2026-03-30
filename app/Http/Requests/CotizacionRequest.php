<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            // 🔹 Cliente
            'cliente_id' => 'required|exists:clientes,id',

            // 🔹 Items
            'items' => 'required|array|min:1',

            // 🔹 Cada item
            'items.*.tipo' => 'required|in:producto,servicio',
            'items.*.cantidad' => 'required|numeric|min:1',

            // 🔹 Validación condicional
            'items.*.producto_id' => 'required_if:items.*.tipo,producto|nullable|exists:productos,id',
            'items.*.servicio_id' => 'required_if:items.*.tipo,servicio|nullable|exists:servicios,id',
        ];
    }

    public function messages(): array
    {
        return [

            'cliente_id.required' => 'Seleccione un cliente',
            'cliente_id.exists' => 'Cliente inválido',

            'items.required' => 'Debe agregar items',
            'items.min' => 'Debe agregar al menos un item',

            'items.*.tipo.required' => 'Tipo obligatorio',
            'items.*.tipo.in' => 'Tipo inválido',

            'items.*.cantidad.required' => 'Cantidad obligatoria',
            'items.*.cantidad.min' => 'Cantidad mínima 1',

            'items.*.producto_id.required_if' => 'Seleccione producto',
            'items.*.servicio_id.required_if' => 'Seleccione servicio',
        ];
    }
}