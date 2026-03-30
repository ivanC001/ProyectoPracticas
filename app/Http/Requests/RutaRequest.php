<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RutaRequest extends FormRequest
{
    /**
     * AUTORIZACIÓN
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * REGLAS
     */
    public function rules(): array
    {
        return [

            // 📅 FECHAS
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',

            // 📍 RUTA
            'origen'  => 'required|string|max:255',
            'destino' => 'required|string|max:255',

            // 👤 RELACIONES
            'conductor_id' => 'required|exists:conductores,id',
            'camion_id'    => 'required|exists:camiones,id',

            // 💰 COSTOS
            'caja_chica'      => 'nullable|numeric|min:0',
            'pago_viaje'      => 'nullable|numeric|min:0',
            'ganancia_viaje'  => 'nullable|numeric|min:0',

            // 🔄 ESTADO
            'estado' => 'required|in:pendiente,en curso,finalizado,cancelado',

            // 📝 EXTRA
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    /**
     * MENSAJES PERSONALIZADOS
     */
    public function messages(): array
    {
        return [

            // FECHAS
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.date'     => 'Formato inválido en fecha de inicio',

            'fecha_fin.required'    => 'La fecha de fin es obligatoria',
            'fecha_fin.date'        => 'Formato inválido en fecha de fin',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser menor que la de inicio',

            // RUTA
            'origen.required' => 'El origen es obligatorio',
            'destino.required' => 'El destino es obligatorio',

            // RELACIONES
            'conductor_id.required' => 'Debe seleccionar un conductor',
            'conductor_id.exists'   => 'El conductor seleccionado no es válido',

            'camion_id.required' => 'Debe seleccionar un vehículo',
            'camion_id.exists'   => 'El vehículo seleccionado no es válido',

            // COSTOS
            'caja_chica.numeric' => 'Caja chica debe ser numérica',
            'caja_chica.min'     => 'Caja chica no puede ser negativa',

            'pago_viaje.numeric' => 'Pago de viaje debe ser numérico',
            'pago_viaje.min'     => 'Pago de viaje no puede ser negativo',

            'ganancia_viaje.numeric' => 'Ganancia debe ser numérica',
            'ganancia_viaje.min'     => 'Ganancia no puede ser negativa',

            // ESTADO
            'estado.required' => 'Debe seleccionar un estado',
            'estado.in'       => 'Estado inválido',

            // EXTRA
            'observaciones.max' => 'Máximo 500 caracteres en observaciones',
        ];
    }

    /**
     * NOMBRES AMIGABLES (BONUS PRO)
     */
    public function attributes(): array
    {
        return [
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
            'origen' => 'origen',
            'destino' => 'destino',
            'conductor_id' => 'conductor',
            'camion_id' => 'vehículo',
            'caja_chica' => 'caja chica',
            'pago_viaje' => 'pago de viaje',
            'ganancia_viaje' => 'ganancia',
            'estado' => 'estado',
            'observaciones' => 'observaciones',
        ];
    }
}