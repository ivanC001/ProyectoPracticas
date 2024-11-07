<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViaticoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Obtener el ID del viático que se está editando, si existe
        $viaticoId = $this->route('id'); // El 'id' viene de la ruta

        return [
            'ruta_id' => 'required|exists:rutas,id',
            'nombre_servicio' => 'required|string|max:255',
            'fecha' => 'required|date',
            'numero_factura' => [
                'required',
                'string',
                'max:255',
                Rule::unique('viaticos')->ignore($viaticoId)->whereNull('deleted_at'),
            ],
            'importe' => 'required|numeric',
            'descripcion' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ruta_id.required' => 'La ruta es obligatoria.',
            'ruta_id.exists' => 'La ruta seleccionada no existe.',
            'nombre_servicio.required' => 'El nombre del servicio es obligatorio.',
            'nombre_servicio.string' => 'El nombre del servicio debe ser una cadena de texto.',
            'nombre_servicio.max' => 'El nombre del servicio no debe exceder los 255 caracteres.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha no es válida.',
            'numero_factura.required' => 'El número de factura es obligatorio.',
            'numero_factura.string' => 'El número de factura debe ser una cadena de texto.',
            'numero_factura.max' => 'El número de factura no debe exceder los 255 caracteres.',
            'numero_factura.unique' => 'El número de factura ya ha sido registrado.',
            'importe.required' => 'El importe es obligatorio.',
            'importe.numeric' => 'El importe debe ser un número válido.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.'
        ];
    }
}
