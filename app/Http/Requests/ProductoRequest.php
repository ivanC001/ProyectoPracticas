<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    /**
     * AUTORIZACIÓN
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * REGLAS DE VALIDACIÓN
     */
    public function rules(): array
    {
        return [

            'descripcion' => 'required|string|max:255',
            'categoria'   => 'string|max:100',
            'unidad'      => 'nullable|string|max:10',

            'precio'      => 'required|numeric|min:0',
            'stock'       => 'required|numeric|min:0',

        ];
    }

    /**
     * MENSAJES PERSONALIZADOS
     */
    public function messages(): array
    {
        return [

            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.max'      => 'Máximo 255 caracteres',


            'precio.required'      => 'El precio es obligatorio',
            'precio.numeric'       => 'El precio debe ser numérico',
            'precio.min'           => 'El precio no puede ser negativo',

            'stock.required'       => 'El stock es obligatorio',
            'stock.numeric'        => 'El stock debe ser numérico',
            'stock.min'            => 'El stock no puede ser negativo',

        ];
    }

    /**
     * ATRIBUTOS (OPCIONAL - PARA MENSAJES MÁS BONITOS)
     */
    public function attributes(): array
    {
        return [
            'descripcion' => 'descripción',
            'categoria'   => 'categoría',
            'precio'      => 'precio',
            'stock'       => 'stock',
        ];
    }
}