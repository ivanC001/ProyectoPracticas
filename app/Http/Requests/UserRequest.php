<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('usuario') ?? $this->route('id');
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH'], true);

        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'rol' => ['required', Rule::in(array_keys(config('roles.definitions', [])))],
            'activo' => ['nullable', 'boolean'],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato valido.',
            'email.unique' => 'Ese correo ya esta registrado.',
            'rol.required' => 'Seleccione un rol.',
            'rol.in' => 'El rol seleccionado no es valido.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.min' => 'La contrasena debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmacion de la contrasena no coincide.',
        ];
    }
}
