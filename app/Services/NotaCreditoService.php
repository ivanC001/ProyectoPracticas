<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\NotasCreditoModel\Nota;
use App\Models\VentasModel\SerieCorrelativo;
use App\Models\VentasModel\Venta;

class NotaCreditoService
{
    public function guardarNotaPendiente($data)
    {
        return DB::transaction(function () use ($data) {

            // 🔥 obtener venta original
            $venta = Venta::findOrFail($data['venta_id']);

            // 🔥 correlativo seguro (07 crédito | 08 débito)
            $correlativo = SerieCorrelativo::obtenerSiguienteCorrelativo(
                $data['tipo_documento']
            );

            return Nota::create([

                'venta_id' => $venta->id,

                'tipo_documento' => $data['tipo_documento'], // 07 o 08

                'serie' => $correlativo['serie'],
                'correlativo' => $correlativo['correlativo'],
                'numero_comprobante' => $correlativo['numero_comprobante'],

                'fecha_emision' => now(),

                // documento afectado
                'tipDocAfectado' => $venta->tipo_documento,
                'numDocAfectado' => $venta->numero_comprobante,

                // motivo
                'codMotivo' => $data['codMotivo'],
                'desMotivo' => $data['desMotivo'],

                // total (puedes mejorar luego)
                'total' => $venta->total_venta,

                'estado_envio' => 'pendiente'
            ]);
        });
    }
}