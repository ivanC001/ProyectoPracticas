<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CamionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // Cambia esto a `true` si quieres permitir la autorización por defecto.
        return true;
    }

    /**
     * Obtén las reglas de validación que se aplicarán a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha_ingreso' => 'required|date',
            'placa_tracto' => 'required|string|max:10',
            'placa_carreto' => 'required|string|max:10',
            'color' => 'required|string|max:50',
            'mtc' => 'required|string|max:20',
            'foto_camino' => 'nullable|image|max:2048',  // Imagen opcional de hasta 2MB
        ];
    }
}
