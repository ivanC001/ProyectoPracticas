<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RutaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'origen' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'conductor_id' => [
                'required',
                Rule::exists('conductores', 'id')->whereNull('deleted_at'),
            ],
            'camion_id' => [
                'nullable',
                Rule::exists('camiones', 'id')->whereNull('deleted_at'),
            ],
            'caja_chica' => 'nullable|numeric|min:0',
            'pago_viaje' => 'nullable|numeric|min:0',
            'ganancia_viaje' => 'nullable|numeric|min:0',
            'estado' => 'required|in:pendiente,en curso,finalizado,cancelado',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.date' => 'Formato invalido en fecha de inicio',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.date' => 'Formato invalido en fecha de fin',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser menor que la de inicio',
            'origen.required' => 'El origen es obligatorio',
            'destino.required' => 'El destino es obligatorio',
            'conductor_id.required' => 'Debe seleccionar un conductor',
            'conductor_id.exists' => 'El conductor seleccionado no es valido',
            'camion_id.exists' => 'El vehiculo seleccionado no es valido',
            'caja_chica.numeric' => 'Caja chica debe ser numerica',
            'caja_chica.min' => 'Caja chica no puede ser negativa',
            'pago_viaje.numeric' => 'Pago de viaje debe ser numerico',
            'pago_viaje.min' => 'Pago de viaje no puede ser negativo',
            'ganancia_viaje.numeric' => 'Ganancia debe ser numerica',
            'ganancia_viaje.min' => 'Ganancia no puede ser negativa',
            'estado.required' => 'Debe seleccionar un estado',
            'estado.in' => 'Estado invalido',
            'observaciones.max' => 'Maximo 500 caracteres en observaciones',
        ];
    }

    public function attributes(): array
    {
        return [
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
            'origen' => 'origen',
            'destino' => 'destino',
            'conductor_id' => 'conductor',
            'camion_id' => 'vehiculo',
            'caja_chica' => 'caja chica',
            'pago_viaje' => 'pago de viaje',
            'ganancia_viaje' => 'ganancia',
            'estado' => 'estado',
            'observaciones' => 'observaciones',
        ];
    }
}
