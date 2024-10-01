<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class ViaticoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ruta_id' => 'required|exists:rutas,id',
            'nombre_servicio' => 'required|string|max:255',
            'fecha' => 'required|date',
            'numero_factura' => [
                'required',
                'string',
                'max:255',
                Rule::unique('viaticos')->whereNull('deleted_at'),
            ],
            'importe' => 'required|numeric',
            'descripcion' => 'nullable|string',
        ];
    }
}
