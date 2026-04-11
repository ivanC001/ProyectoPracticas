<?php

namespace App\Services;

use App\Models\NotasCreditoModel\Nota;
use App\Models\VentasModel\SerieCorrelativo;
use App\Models\VentasModel\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotaCreditoService
{
    public function guardarNotaPendiente(array $data): Nota
    {
        return DB::transaction(function () use ($data) {
            $venta = Venta::query()->findOrFail($data['venta_id']);

            if ($venta->estado_envio !== 'aceptado') {
                throw ValidationException::withMessages([
                    'venta_id' => ['Solo se puede generar notas sobre comprobantes aceptados por SUNAT.'],
                ]);
            }

            if (!in_array((string) $venta->tipo_documento, ['01', '03'], true)) {
                throw ValidationException::withMessages([
                    'venta_id' => ['El comprobante seleccionado no admite nota de credito/debito.'],
                ]);
            }

            $correlativo = SerieCorrelativo::obtenerSiguienteCorrelativo(
                (string) $data['tipo_documento']
            );

            return Nota::query()->create([
                'venta_id' => $venta->id,
                'emisor_user_id' => $data['emisor_user_id'] ?? null,
                'tipo_documento' => $data['tipo_documento'],
                'serie' => $correlativo['serie'],
                'correlativo' => $correlativo['correlativo'],
                'numero_comprobante' => $correlativo['numero_comprobante'],
                'fecha_emision' => now(),
                'tipDocAfectado' => $venta->tipo_documento,
                'numDocAfectado' => $venta->numero_comprobante,
                'codMotivo' => $data['codMotivo'],
                'desMotivo' => $data['desMotivo'],
                'total' => (float) ($data['monto_nota'] ?? $venta->total_venta),
                'estado_envio' => 'pendiente',
            ]);
        });
    }
}
