<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CamionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $camionId = $this->route('id');

        return [
            'fecha_ingreso' => 'required|date',
            'placa_tracto' => [
                'required',
                'string',
                'max:10',
                Rule::unique('camiones', 'placa_tracto')->ignore($camionId),
            ],
            'placa_carreto' => [
                'required',
                'string',
                'max:10',
                Rule::unique('camiones', 'placa_carreto')->ignore($camionId),
            ],
            'color' => 'required|string|max:50',
            'mtc' => [
                'required',
                'string',
                'max:20',
                Rule::unique('camiones', 'mtc')->ignore($camionId),
            ],
            'foto_camino' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
            'placa_tracto.required' => 'La placa del tracto es obligatoria',
            'placa_tracto.unique' => 'La placa del tracto ya existe',
            'placa_carreto.required' => 'La placa del trailer es obligatoria',
            'placa_carreto.unique' => 'La placa del trailer ya existe',
            'color.required' => 'El color es obligatorio',
            'mtc.required' => 'El codigo MTC es obligatorio',
            'mtc.unique' => 'El codigo MTC ya existe',
        ];
    }
}
