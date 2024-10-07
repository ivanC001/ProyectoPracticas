<?php

namespace App\Domains\Reportes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReporteRequest extends FormRequest
{
    public function authorize()
    {
        // Permitir que cualquier usuario haga esta solicitud (cambiar según sea necesario)
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'nullable|integer',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'exportar' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'id.integer' => 'El ID debe ser un número entero.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'exportar.integer' => 'El valor de exportar debe ser un número entero.',
        ];
    }
}
