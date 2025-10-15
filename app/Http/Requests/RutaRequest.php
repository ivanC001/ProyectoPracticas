<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RutaRequest extends FormRequest
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
            // 'fecha_inicio' => 'required|date',
            // 'fecha_fin' => 'required|date',
            // 'origen' => 'required|string|max:255',
            // 'destino' => 'required|string|max:255',
            // 'conductor_id' => [
            //     'required',
            //     'exists:conductores,id,deleted_at,NULL',
            // ],
            // 'camion_id' => [
            //     'required',
            //     'exists:camiones,id,deleted_at,NULL',
            // ],
            'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        'origen' => 'required|string|max:255',
        'destino' => 'nullable|string|max:255',

        'conductor_id' => 'required|exists:conductores,id',
        'camion_id' => 'required|exists:camiones,id',

        'caja_chica' => 'nullable|numeric|min:0',
        'pago_viaje' => 'nullable|numeric|min:0',
        'ganancia_viaje' => 'nullable|numeric|min:0',

        'estado' => 'required|string|in:pendiente,en curso,finalizado,cancelado',
        'observaciones' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'origen.required' => 'El origen es obligatorio.',
            'destino.required' => 'El destino es obligatorio.',
            'conductor_id.required' => 'El conductor es obligatorio.',
            'conductor_id.exists' => 'El conductor seleccionado no es válido o ha sido eliminado.',
            'camion_id.required' => 'El camión es obligatorio.',
            'camion_id.exists' => 'El camión seleccionado no es válido o ha sido eliminado.',
        ];
    }
}
