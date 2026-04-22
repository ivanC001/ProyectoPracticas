<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CombustibleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $rutaIdFromRoute = $this->route('ruta_id') ?? $this->route('ruta');

        if ($rutaIdFromRoute !== null && !$this->filled('ruta_id')) {
            $this->merge([
                'ruta_id' => $rutaIdFromRoute,
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'ruta_id' => 'required|exists:rutas,id',
            'num_factura' => [
                'required',
                'string',
                'max:255',
                Rule::unique('combustibles', 'num_factura')
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],
            'grifo' => 'required|string|max:255',
            'fecha_hora' => 'required|date',
            'galonesCombustible' => 'required|numeric|min:0.01',
            'importe' => 'required|numeric|min:0.01',
            'kilometraje_inicial' => 'required|integer|min:0',
            'kilometraje_final' => 'nullable|integer|min:0|gte:kilometraje_inicial',
            'tipo_combustible' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'ruta_id.required' => 'La ruta es obligatoria.',
            'ruta_id.exists' => 'La ruta seleccionada no es valida.',
            'num_factura.required' => 'El numero de factura es obligatorio.',
            'num_factura.unique' => 'El numero de factura ya ha sido registrado.',
            'grifo.required' => 'El grifo es obligatorio.',
            'fecha_hora.required' => 'La fecha y hora son obligatorias.',
            'fecha_hora.date' => 'La fecha y hora debe tener un formato valido.',
            'galonesCombustible.required' => 'Los galones son obligatorios.',
            'galonesCombustible.numeric' => 'Los galones deben ser numericos.',
            'galonesCombustible.min' => 'Los galones deben ser mayores a cero.',
            'importe.required' => 'El importe es obligatorio.',
            'importe.numeric' => 'El importe debe ser numerico.',
            'importe.min' => 'El importe debe ser mayor a cero.',
            'kilometraje_inicial.required' => 'El kilometraje inicial es obligatorio.',
            'kilometraje_inicial.integer' => 'El kilometraje inicial debe ser entero.',
            'kilometraje_inicial.min' => 'El kilometraje inicial no puede ser negativo.',
            'kilometraje_final.integer' => 'El kilometraje final debe ser entero.',
            'kilometraje_final.min' => 'El kilometraje final no puede ser negativo.',
            'kilometraje_final.gte' => 'El kilometraje final no puede ser menor al inicial.',
            'tipo_combustible.required' => 'El tipo de combustible es obligatorio.',
            'tipo_combustible.string' => 'El tipo de combustible debe ser texto.',
        ];
    }
}
