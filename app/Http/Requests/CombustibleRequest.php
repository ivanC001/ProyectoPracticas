<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CombustibleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ruta_id' => 'required|exists:rutas,id',
            // 'num_factura' => [
            //     'required', 
            //     'string', 
            //     'max:255', 
            //     Rule::unique('combustibles')->whereNull('deleted_at')
            // ],
            'grifo' => 'required|string|max:255',
            'fecha_hora' => 'required|date',
            'galonesCombustible' => 'required|numeric',
            'importe' => 'required|numeric',
            'kilometraje_inicial' => 'nullable|integer',
            'kilometraje_final' => 'nullable|integer',
            'tipo_combustible' => 'nullable|string|max:50',
        ];
    }

    // Personalización de los mensajes de error
    public function messages()
    {
        return [
            'ruta_id.required' => 'El campo ruta es obligatorio.',
            'ruta_id.exists' => 'La ruta seleccionada no es válida.',
            'num_factura.required' => 'El número de factura es obligatorio.',
            'num_factura.unique' => 'El número de factura ya ha sido registrado.',
            'grifo.required' => 'El nombre del grifo es obligatorio.',
            'fecha_hora.required' => 'La fecha y hora son obligatorias.',
            'fecha_hora.date' => 'La fecha y hora deben ser una fecha válida.',
            'galonesCombustible.required' => 'La cantidad de galones es obligatoria.',
            'galonesCombustible.numeric' => 'La cantidad de galones debe ser un número.',
            'importe.required' => 'El importe es obligatorio.',
            'importe.numeric' => 'El importe debe ser un número.',
            'kilometraje_inicial.integer' => 'El kilometraje inicial debe ser un número entero.',
            'kilometraje_final.integer' => 'El kilometraje final debe ser un número entero.',
            'tipo_combustible.string' => 'El tipo de combustible debe ser un texto.',
        ];
    }
}
