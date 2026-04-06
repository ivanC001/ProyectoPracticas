<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CamionSeguroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_seguro' => 'required|string|max:100',
            'aseguradora' => 'nullable|string|max:150',
            'numero_poliza' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_vencimiento' => 'required|date',
            'monto' => 'nullable|numeric|min:0',
            'alertar_dias_antes' => 'nullable|integer|min:1|max:365',
            'activo' => 'nullable|boolean',
            'observaciones' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_seguro.required' => 'El tipo de seguro es obligatorio',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria',
            'fecha_vencimiento.date' => 'La fecha de vencimiento no es valida',
            'monto.numeric' => 'El monto debe ser numerico',
            'alertar_dias_antes.integer' => 'Los dias de alerta deben ser numericos',
        ];
    }
}
