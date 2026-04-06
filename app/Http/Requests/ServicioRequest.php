<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
            'duracion_estimada' => 'nullable|integer|min:0',
            'requiere_personal' => 'nullable|boolean',
            'cantidad_personal' => 'nullable|integer|min:0',
            'requiere_equipo' => 'nullable|boolean',
            'equipos_descripcion' => 'nullable|string',
            'tipo_servicio' => 'nullable|string',
            'requiere_transporte' => 'nullable|boolean',
            'condiciones' => 'nullable|string',
            'requisitos_cliente' => 'nullable|string',
            'garantia_dias' => 'nullable|integer|min:0',
            'nivel_servicio' => 'nullable|in:basico,estandar,premium',
            'prioridad' => 'nullable|in:baja,media,alta',
            'instrucciones' => 'nullable|string',
            'observaciones_internas' => 'nullable|string',
            'frecuencia' => 'nullable|in:unico,recurrente',
            'recurrente_cada' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'precio.required' => 'El precio es obligatorio',
            'precio.numeric' => 'El precio debe ser numerico',
        ];
    }
}
