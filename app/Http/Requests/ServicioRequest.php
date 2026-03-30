<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicioRequest extends FormRequest
{
    /**
     * Autorizar request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        $id = $this->route('servicio'); // para update

        return [

            // 🔹 Básico
            'codigo' => "nullable|string|max:50|unique:servicios,codigo,$id",
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
            'duracion_estimada' => 'nullable|integer|min:0',

            // 🔹 Recursos
            'requiere_personal' => 'nullable|boolean',
            'cantidad_personal' => 'nullable|integer|min:0',
            'requiere_equipo' => 'nullable|boolean',
            'equipos_descripcion' => 'nullable|string',

            // 🔹 Ubicación
            'tipo_servicio' => 'nullable|string',
            'requiere_transporte' => 'nullable|boolean',

            // 🔹 Comercial
            'condiciones' => 'nullable|string',
            'requisitos_cliente' => 'nullable|string',
            'garantia_dias' => 'nullable|integer|min:0',

            // 🔹 Clasificación
            'nivel_servicio' => 'nullable|in:basico,estandar,premium',
            'prioridad' => 'nullable|in:baja,media,alta',

            // 🔹 Otros
            'instrucciones' => 'nullable|string',
            'observaciones_internas' => 'nullable|string',
            'frecuencia' => 'nullable|in:unico,recurrente',
            'recurrente_cada' => 'nullable|string',
        ];
    }

    /**
     * Mensajes personalizados
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'precio.required' => 'El precio es obligatorio',
            'precio.numeric' => 'El precio debe ser numérico',
            'codigo.unique' => 'El código ya existe',
        ];
    }
}