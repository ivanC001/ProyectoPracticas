<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConductorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $conductorId = $this->route('id');

        return [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'required|in:Masculino,Femenino',
            'licencia' => [
                'required',
                'string',
                'max:20',
                Rule::unique('conductores', 'licencia')->ignore($conductorId),
            ],
            'tipo_licencia' => 'required|in:A,B,C,D,E',
            'telefono' => 'nullable|string|max:15',
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('conductores', 'email')->ignore($conductorId),
            ],
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'camion_id' => [
                'required',
                Rule::exists('camiones', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'apellido.required' => 'El apellido es obligatorio',
            'genero.required' => 'Seleccione el genero',
            'genero.in' => 'Genero invalido',
            'licencia.required' => 'La licencia es obligatoria',
            'licencia.unique' => 'La licencia ya esta registrada',
            'tipo_licencia.required' => 'Seleccione el tipo de licencia',
            'tipo_licencia.in' => 'Tipo de licencia invalido',
            'email.email' => 'El email no es valido',
            'email.unique' => 'El email ya esta registrado',
            'camion_id.required' => 'Debe asignar un tracto y trailer al conductor',
            'camion_id.exists' => 'La unidad seleccionada no es valida',
        ];
    }
}
