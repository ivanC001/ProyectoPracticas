<?php

namespace App\Services;

use App\Models\VentasModel\DetalleVenta;
use App\Models\VentasModel\SerieCorrelativo;
use App\Models\VentasModel\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VentaService
{
    public function guardarVenta($data, $invoice, $totales, $hash, $sunatResponse, $rutaXml, $rutaPdf, $rutaCdr)
    {
        $numeroComprobante = $invoice->getSerie() . '-' . $invoice->getCorrelativo();

        return Venta::create([
            'tipo_documento' => $data['tipo_documento'],
            'tipo_operacion' => '0101',

            'serie' => $invoice->getSerie(),
            'correlativo' => $invoice->getCorrelativo(),
            'numero_comprobante' => $numeroComprobante,

            'fecha_emision' => $data['fecha_emision'],
            'moneda' => $data['moneda'],

            'tipo_documento_cliente' => $data['cliente']['tipo_doc'],
            'numero_documento_cliente' => $data['cliente']['num_doc'],
            'nombre_cliente' => $data['cliente']['razon_social'],
            'emisor_user_id' => $data['emisor_user_id'] ?? null,

            'total_venta' => $totales['total'],
            'total_impuestos' => $totales['igv'],

            'codigo_respuesta_sunat' => $sunatResponse['cdrRespuesta']['code'] ?? null,
            'descripcion_respuesta_sunat' => $sunatResponse['cdrRespuesta']['description'] ?? null,

            'hash_cpe' => $hash,

            'archivo_xml' => $rutaXml,
            'archivo_pdf' => $rutaPdf,
            'cdr_zip' => $rutaCdr,

            'estado_envio' => $sunatResponse['success'] ? 'aceptado' : 'rechazado',
        ]);
    }

    public function guardarVentaPendiente($data)
    {
        return DB::transaction(function () use ($data) {
            $igvCatalogService = new SunatIgvCatalogService();
            $cliente = (array) ($data['cliente'] ?? []);
            $formaPago = strtolower((string) ($data['forma_pago'] ?? 'contado')) === 'credito'
                ? 'credito'
                : 'contado';

            $correlativo = SerieCorrelativo::obtenerSiguienteCorrelativo(
                $data['tipo_documento']
            );

            $nombreCliente = trim((string) ($cliente['razon_social'] ?? ''));
            $nombreCliente = $nombreCliente !== '' ? $nombreCliente : 'CLIENTES VARIOS';

            $venta = Venta::create([
                'tipo_documento' => $data['tipo_documento'],
                'tipo_operacion' => '0101',

                'serie' => $correlativo['serie'],
                'correlativo' => $correlativo['correlativo'],
                'numero_comprobante' => $correlativo['numero_comprobante'],

                'fecha_emision' => $data['fecha_emision'],
                'moneda' => $data['moneda'],
                'forma_pago' => $formaPago,

                'tipo_documento_cliente' => $cliente['tipo_doc'] ?? null,
                'numero_documento_cliente' => $cliente['num_doc'] ?? null,
                'nombre_cliente' => $nombreCliente,
                'emisor_user_id' => $data['emisor_user_id'] ?? null,

                'estado_envio' => 'pendiente',
                'total_venta' => 0,
                'total_impuestos' => 0,
            ]);

            $itemsForTotals = [];

            foreach ($data['items'] as $item) {
                $line = $igvCatalogService->calculateLine($item);
                $cantidad = max((float) ($item['cantidad'] ?? 0), 0);
                $tipoItem = (string) ($item['tipo_item'] ?? 'producto');
                $itemId = (int) ($item['item_id'] ?? 0);
                $codigo = trim((string) ($item['codigo'] ?? ''));

                if ($codigo === '') {
                    $codigo = $tipoItem === 'servicio'
                        ? ('SERV-' . $itemId)
                        : ('PROD-' . $itemId);
                }

                $itemsForTotals[] = [
                    'cantidad' => $cantidad,
                    'valor_unitario' => (float) ($item['valor_unitario'] ?? 0),
                    'descuento' => (float) ($item['descuento'] ?? 0),
                    'tip_afe_igv' => $line['tip_afe_igv'],
                ];

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'tipo_item' => $tipoItem,
                    'item_id' => $itemId > 0 ? $itemId : null,
                    'codigo_producto' => $codigo,
                    'descripcion' => $item['descripcion'],
                    'unidad' => $item['unidad'] ?? ($tipoItem === 'servicio' ? 'ZZ' : 'NIU'),
                    'tip_afe_igv' => $line['tip_afe_igv'],

                    'cantidad' => $cantidad,
                    'valor_unitario' => $item['valor_unitario'],
                    'precio_unitario' => $line['mto_precio_unitario_sunat'],

                    'descuento' => $line['descuento'],
                    'subtotal' => $line['subtotal'],
                    'igv' => $line['igv'],
                    'total' => $line['total'],
                ]);
            }

            $totales = $igvCatalogService->calculateTotals($itemsForTotals);
            $totalServicios = $this->calculateTotalServicios((array) ($data['items'] ?? []));
            $detraccionData = $this->buildDetraccionData($data, $totalServicios);
            $creditoData = $this->buildCreditoData($data, $totales, (float) ($detraccionData['monto'] ?? 0));
            $observacion = trim((string) ($data['observacion'] ?? ''));

            if ($observacion === '' && ($detraccionData['aplica'] ?? false)) {
                $observacion = 'OPERACION SUJETA AL SPOT ' . number_format((float) $detraccionData['porcentaje'], 2) . '%';
            }

            $venta->update([
                'tipo_operacion' => ($detraccionData['aplica'] ?? false) ? '1001' : '0101',
                'total_venta' => $totales['total'],
                'total_impuestos' => $totales['igv'],
                'credito_total_cuotas' => $creditoData['cuotas'],
                'credito_monto_pendiente' => $creditoData['monto_pendiente'],
                'credito_fecha_vencimiento' => $creditoData['fecha_vencimiento'],
                'detraccion_aplica' => $detraccionData['aplica'],
                'detraccion_codigo' => $detraccionData['codigo'],
                'detraccion_porcentaje' => $detraccionData['porcentaje'],
                'detraccion_monto' => $detraccionData['monto'],
                'detraccion_cuenta' => $detraccionData['cuenta'],
                'detraccion_medio_pago' => $detraccionData['medio_pago'],
                'observacion' => $observacion !== '' ? $observacion : null,
            ]);

            return $venta->fresh('detalles');
        });
    }

    protected function buildDetraccionData(array $data, float $totalServicios): array
    {
        $detraccionInput = (array) ($data['detraccion'] ?? []);
        $aplica = in_array(
            ($detraccionInput['aplica'] ?? false),
            [true, 1, '1', 'true', 'on', 'yes'],
            true
        );
        $montoMinimo = (float) config('sunat_detraccion.monto_minimo_servicios', 700);

        if (!$aplica || $totalServicios <= $montoMinimo) {
            return [
                'aplica' => false,
                'codigo' => null,
                'porcentaje' => null,
                'monto' => null,
                'cuenta' => null,
                'medio_pago' => null,
            ];
        }

        $codigo = (string) ($detraccionInput['codigo'] ?? '');
        $catalogo = config('sunat_detraccion.servicios', []);
        $porcentajeCatalogo = (float) data_get($catalogo[$codigo] ?? [], 'porcentaje', 0);
        $porcentajeInput = (float) ($detraccionInput['porcentaje'] ?? 0);
        $porcentaje = $porcentajeInput > 0 ? $porcentajeInput : $porcentajeCatalogo;
        $montoCalculado = round($totalServicios * ($porcentaje / 100), 2);
        $montoInput = (float) ($detraccionInput['monto'] ?? 0);
        $monto = $montoInput > 0
            ? round(min($montoInput, $totalServicios), 2)
            : $montoCalculado;
        $cuenta = preg_replace('/\D+/', '', (string) ($detraccionInput['cuenta'] ?? '')) ?? '';

        if ($cuenta === '') {
            $cuenta = preg_replace('/\D+/', '', (string) config('sunat_detraccion.cuenta_bn_default', '')) ?? '';
        }

        return [
            'aplica' => true,
            'codigo' => $codigo !== '' ? $codigo : null,
            'porcentaje' => $porcentaje > 0 ? $porcentaje : null,
            'monto' => $monto > 0 ? $monto : null,
            'cuenta' => $cuenta !== '' ? $cuenta : null,
            'medio_pago' => (string) ($detraccionInput['medio_pago'] ?? config('sunat_detraccion.medio_pago_default', '001')),
        ];
    }

    protected function calculateTotalServicios(array $items): float
    {
        $igvCatalogService = new SunatIgvCatalogService();

        return (float) collect($items)->reduce(function ($carry, $item) use ($igvCatalogService) {
            if ((string) data_get($item, 'tipo_item') !== 'servicio') {
                return $carry;
            }

            $line = $igvCatalogService->calculateLine((array) $item);
            return $carry + (float) ($line['total'] ?? 0);
        }, 0.0);
    }

    protected function buildCreditoData(array $data, array $totales, float $detraccionMonto): array
    {
        $formaPago = strtolower((string) ($data['forma_pago'] ?? 'contado'));

        if ($formaPago !== 'credito') {
            return [
                'cuotas' => null,
                'monto_pendiente' => null,
                'fecha_vencimiento' => null,
            ];
        }

        $creditoInput = (array) ($data['credito'] ?? []);
        $total = (float) ($totales['total'] ?? 0);

        $montoPendienteInput = (float) ($creditoInput['monto_pendiente'] ?? 0);
        $montoPendiente = $montoPendienteInput > 0
            ? $montoPendienteInput
            : max($total - $detraccionMonto, 0);

        $cuotas = max((int) ($creditoInput['cuotas'] ?? 1), 1);
        $fechaVencimiento = $creditoInput['fecha_vencimiento'] ?? null;

        if (!$fechaVencimiento && !empty($data['fecha_emision'])) {
            $fechaVencimiento = Carbon::parse((string) $data['fecha_emision'])->addDays(30)->toDateString();
        }

        return [
            'cuotas' => $cuotas,
            'monto_pendiente' => round(min($montoPendiente, $total), 2),
            'fecha_vencimiento' => $fechaVencimiento,
        ];
    }
}
