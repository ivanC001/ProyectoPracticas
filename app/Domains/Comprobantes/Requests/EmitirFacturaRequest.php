<?php

namespace App\Domains\Comprobantes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmitirFacturaRequest extends FormRequest
{
    public function authorize()
    {
        return true;  
    }

    public function rules()
    {
        $rules = [
            'tipo_comprobante' => 'required|string|in:01,03',  // '01' para factura, '03' para boleta
            'cliente.tipo_doc' => 'required|string',  
            'cliente.num_doc' => 'nullable|string',  
            'cliente.razon_social' => 'nullable|string|max:255',  

            'detalle' => 'required|array',  
            'detalle.*.unidad' => 'required|string',   
            'detalle.*.cantidad' => 'required|numeric|min:1',  
            'detalle.*.codigo' => 'required|string',    
            'detalle.*.descripcion' => 'required|string', 
            'detalle.*.valor_unitario' => 'required|numeric|min:0',  

            'factura.moneda' => 'required|string|in:PEN,USD'
        ];

        // Ajustar reglas en función del tipo de comprobante
        if ($this->input('tipo_comprobante') === '01') {  // Factura
            $rules['cliente.num_doc'] = 'required|string';
            $rules['cliente.razon_social'] = 'required|string';
        } elseif ($this->input('tipo_comprobante') === '03') {
            $rules['cliente.num_doc'] = 'required_if:factura.mto_total,>,700|string';
            $rules['cliente.razon_social'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'tipo_comprobante.required' => 'El tipo de comprobante es requerido.',
            'tipo_comprobante.in' => 'El tipo de comprobante debe ser "01" para factura o "03" para boleta.',
            'cliente.num_doc.required_if' => 'El número de documento es obligatorio para boletas con un monto mayor a 700 soles.',
            'cliente.razon_social.required' => 'La razón social del cliente es requerida.',
            'detalle.required' => 'Debe incluir al menos un producto en el detalle.',
            'detalle.*.unidad.required' => 'La unidad de medida es requerida para cada producto.',
            'detalle.*.cantidad.required' => 'La cantidad de cada producto es requerida.',
            'detalle.*.cantidad.numeric' => 'La cantidad debe ser un número.',
            'detalle.*.valor_unitario.required' => 'El valor unitario es requerido para cada producto.',
            'factura.moneda.required' => 'La moneda es requerida y debe ser "PEN" o "USD".',
            'factura.moneda.in' => 'La moneda debe ser "PEN" para soles o "USD" para dólares.',
        ];
    }
}
