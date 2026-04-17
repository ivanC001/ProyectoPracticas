<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asunto' => 'nullable|string|max:255',
            'fecha' => 'nullable|date',
            'descripcion_general' => 'nullable|string',
            'notas' => 'nullable|string',
            'medios_pago' => 'nullable|array',
            'medios_pago.*' => ['string', Rule::in(array_keys(config('empresa.medios_pago', [])))],
            'incluye_igv' => 'nullable|boolean',
            'estado' => 'nullable|in:borrador,aprobado,rechazado',
            'cliente_id' => 'required|exists:clientes,id',
            'items' => 'required|array|min:1',
            'items.*.tipo' => 'required|in:producto,servicio',
            'items.*.cantidad' => 'required|numeric|min:1',
            'items.*.producto_id' => 'required_if:items.*.tipo,producto|nullable|exists:productos,id',
            'items.*.servicio_id' => 'required_if:items.*.tipo,servicio|nullable|exists:servicios,id',
            'items.*.detalle_servicio' => 'nullable|array',
            'items.*.detalle_servicio.*' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'asunto.max' => 'El asunto no debe exceder 255 caracteres',
            'fecha.date' => 'La fecha no es valida',
            'descripcion_general.string' => 'La descripcion general debe ser texto',
            'notas.string' => 'Las notas deben ser texto',
            'medios_pago.array' => 'Los medios de pago deben enviarse como lista',
            'medios_pago.*.in' => 'Seleccione un medio de pago valido',
            'incluye_igv.boolean' => 'El indicador de IGV no es valido',
            'estado.in' => 'Estado invalido',
            'cliente_id.required' => 'Seleccione un cliente',
            'cliente_id.exists' => 'Cliente invalido',
            'items.required' => 'Debe agregar items',
            'items.min' => 'Debe agregar al menos un item',
            'items.*.tipo.required' => 'Tipo obligatorio',
            'items.*.tipo.in' => 'Tipo invalido',
            'items.*.cantidad.required' => 'Cantidad obligatoria',
            'items.*.cantidad.min' => 'Cantidad minima 1',
            'items.*.producto_id.required_if' => 'Seleccione producto',
            'items.*.servicio_id.required_if' => 'Seleccione servicio',
            'items.*.detalle_servicio.array' => 'El detalle del item debe enviarse como lista',
            'items.*.detalle_servicio.*.max' => 'Cada linea del detalle debe tener maximo 255 caracteres',
        ];
    }
}
