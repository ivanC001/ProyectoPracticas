<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descripcion' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:100',
            'unidad' => 'nullable|string|max:10',
            'precio' => 'required|numeric|min:0',
            'moneda_precio' => 'required|in:PEN,USD',
            'stock' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'descripcion.required' => 'La descripcion es obligatoria',
            'descripcion.max' => 'Maximo 255 caracteres',

            'precio.required' => 'El precio es obligatorio',
            'precio.numeric' => 'El precio debe ser numerico',
            'precio.min' => 'El precio no puede ser negativo',

            'moneda_precio.required' => 'Debe seleccionar la moneda del precio',
            'moneda_precio.in' => 'La moneda del precio debe ser PEN o USD',

            'stock.required' => 'El stock es obligatorio',
            'stock.numeric' => 'El stock debe ser numerico',
            'stock.min' => 'El stock no puede ser negativo',
        ];
    }

    public function attributes(): array
    {
        return [
            'descripcion' => 'descripcion',
            'categoria' => 'categoria',
            'precio' => 'precio',
            'moneda_precio' => 'moneda del precio',
            'stock' => 'stock',
        ];
    }
}

