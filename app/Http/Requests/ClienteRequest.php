<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteRequest extends FormRequest
{
    /**
     * Autorizar request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas base
     */
    public function rules(): array
    {
        $clienteId = $this->route('cliente');

        return [
            'tipo_doc' => 'required|in:0,1,6',
            'razon_social' => 'required|string|max:255',

            'num_doc' => [
                'required',
                'numeric',
                'unique:clientes,num_doc,' . $clienteId,
            ],

            'direccion' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
        ];
    }

    /**
     * Mensajes personalizados
     */
    public function messages(): array
    {
        return [
            'tipo_doc.required' => 'El tipo de documento es obligatorio',
            'tipo_doc.in' => 'Tipo de documento inválido',

            'num_doc.required' => 'El número de documento es obligatorio',
            'num_doc.numeric' => 'El número de documento debe ser numérico',
            'num_doc.unique' => 'El número de documento ya está registrado',

            'razon_social.required' => 'La razón social es obligatoria',
        ];
    }

    /**
     * Validación adicional (DINÁMICA 🔥)
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $tipo = $this->tipo_doc;
            $num = $this->num_doc;

            // 🔹 DNI
            if ($tipo == '1') {
                if (strlen($num) != 8) {
                    $validator->errors()->add('num_doc', 'El DNI debe tener 8 dígitos');
                }
            }

            // 🔹 RUC
            if ($tipo == '6') {

                if (strlen($num) != 11) {
                    $validator->errors()->add('num_doc', 'El RUC debe tener 11 dígitos');
                }

                // Validación básica SUNAT (estructura)
                if (!preg_match('/^(10|15|17|20)\d{9}$/', $num)) {
                    $validator->errors()->add('num_doc', 'El RUC no es válido');
                }
            }
        });
    }
}