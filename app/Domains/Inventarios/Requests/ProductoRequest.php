<?php

namespace App\Domains\Inventarios\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Permitir acceso, puedes modificar según tus necesidades
    }

    public function rules()
    {
        // Determinar si es un POST (creación) o PUT/PATCH (actualización)
        if ($this->isMethod('post')) {
            // Reglas para la creación
            return [
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'precio' => 'required|numeric',
                'cantidad_stock' => 'required|integer',
            ];
        } else if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Reglas para la actualización (aquí puedes hacerlas opcionales con 'sometimes')
            return [
                'nombre' => 'sometimes|required|string|max:255',
                'descripcion' => 'nullable|string',
                'precio' => 'sometimes|required|numeric',
                'cantidad_stock' => 'sometimes|required|integer',
            ];
        }

        // Si es otro método, no validamos nada, aunque esto podría no ser necesario en la mayoría de los casos
        return [];
    }
}