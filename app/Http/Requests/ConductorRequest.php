<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConductorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'required|in:Masculino,Femenino',
            'licencia' => 'required|string|max:20',
            'tipo_licencia' => 'required|in:A,B,C,D,E',
            'telefono' => 'nullable|string|max:15',
            'email' => 'nullable|string|email|max:100',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
        ];
    }
}
