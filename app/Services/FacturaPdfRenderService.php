<?php

namespace App\Services;

use App\Models\VentasModel\Venta;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Luecano\NumeroALetras\NumeroALetras;

class FacturaPdfRenderService
{
    public function render(Venta $venta): string
    {
        $venta->loadMissing('detalles');

        $igvCatalogService = new SunatIgvCatalogService();

        $itemsForTotals = $venta->detalles->map(function ($detalle) {
            return [
                'cantidad' => (float) $detalle->cantidad,
                'valor_unitario' => (float) $detalle->valor_unitario,
                'descuento' => (float) $detalle->descuento,
                'tip_afe_igv' => $detalle->tip_afe_igv,
            ];
        })->toArray();

        $totales = $igvCatalogService->calculateTotals($itemsForTotals);
        $total = (float) $totales['total'];

        $detallesRender = $venta->detalles->map(function ($detalle) use ($igvCatalogService) {
            $meta = $igvCatalogService->metadata($detalle->tip_afe_igv);

            return [
                'tipo_item' => $detalle->tipo_item ?? 'producto',
                'item_id' => $detalle->item_id,
                'codigo_producto' => $detalle->codigo_producto,
                'descripcion' => $detalle->descripcion,
                'unidad' => $detalle->unidad,
                'tip_afe_igv' => $meta['code'],
                'tip_afe_label' => $meta['label'],
                'aplica_igv' => $meta['group'] === 'gravada',
                'cantidad' => (float) $detalle->cantidad,
                'valor_unitario' => (float) $detalle->valor_unitario,
                'subtotal' => (float) $detalle->subtotal,
                'igv' => (float) $detalle->igv,
                'total' => (float) $detalle->total,
            ];
        })->values()->toArray();

        $formatter = new NumeroALetras();
        $monedaTexto = strtoupper((string) $venta->moneda) === 'USD'
            ? 'DOLARES AMERICANOS'
            : 'SOLES';

        $logoPath = public_path('assets/dist/img/AdminLTELogo.png');
        $logoBase64 = null;

        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
        }

        $detraccionCatalog = config('sunat_detraccion.servicios', []);
        $detraccionCodigo = (string) ($venta->detraccion_codigo ?? '');
        $detraccionMeta = $detraccionCodigo !== ''
            ? ($detraccionCatalog[$detraccionCodigo] ?? null)
            : null;

        $formaPago = strtolower((string) ($venta->forma_pago ?? 'contado'));
        $formaPagoLabel = $formaPago === 'credito' ? 'Credito' : 'Contado';
        $hasServiceItems = $venta->detalles->contains(function ($detalle) {
            return (string) ($detalle->tipo_item ?? 'producto') === 'servicio';
        });
        $cuotasPreview = $this->buildCuotasPreview($venta);

        $html = view('factura.pdf', [
            'venta' => $venta,
            'empresa' => config('empresa'),
            'logoBase64' => $logoBase64,
            'detallesRender' => $detallesRender,
            'hasServiceItems' => $hasServiceItems,
            'tipoDocumentoLabel' => $this->tipoDocumentoLabel($venta->tipo_documento),
            'monedaSimbolo' => strtoupper((string) $venta->moneda) === 'USD' ? 'US$' : 'S/',
            'totales' => $totales,
            'subtotal' => (float) $totales['valor_venta'],
            'igv' => (float) $totales['igv'],
            'total' => $total,
            'totalLetras' => $formatter->toInvoice($total, 2, $monedaTexto),
            'pdfLegends' => $igvCatalogService->buildPdfLegends($totales),
            'hashCpe' => (string) ($venta->hash_cpe ?? ''),
            'qrDataUri' => $this->buildQrDataUri($venta, $totales),
            'formaPagoLabel' => $formaPagoLabel,
            'detraccionMeta' => $detraccionMeta,
            'cuotasPreview' => $cuotasPreview,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    public function fileName(Venta $venta): string
    {
        $number = $venta->numero_comprobante ?: ($venta->serie . '-' . $venta->correlativo);
        $clean = preg_replace('/[^A-Za-z0-9\-]/', '_', (string) $number);

        return 'factura-' . $clean . '.pdf';
    }

    protected function tipoDocumentoLabel(?string $tipoDocumento): string
    {
        return match ((string) $tipoDocumento) {
            '01' => 'FACTURA ELECTRONICA',
            '03' => 'BOLETA ELECTRONICA',
            default => 'COMPROBANTE ELECTRONICO',
        };
    }

    protected function buildQrDataUri(Venta $venta, array $totales): ?string
    {
        try {
            $data = $this->buildSunatQrContent($venta, $totales);

            if ($data === '') {
                return null;
            }

            return Builder::create()
                ->writer(new PngWriter())
                ->data($data)
                ->size(170)
                ->margin(0)
                ->build()
                ->getDataUri();
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    protected function buildSunatQrContent(Venta $venta, array $totales): string
    {
        [$serie, $correlativo] = $this->resolveSerieCorrelativo($venta);

        $rucEmisor = (string) (config('empresa.ruc') ?? '');
        $tipoDocumento = (string) ($venta->tipo_documento ?? '');
        $igv = number_format((float) ($totales['igv'] ?? 0), 2, '.', '');
        $total = number_format((float) ($totales['total'] ?? $venta->total_venta ?? 0), 2, '.', '');
        $fecha = optional($venta->fecha_emision)->format('d/m/Y') ?? '';
        $tipoDocCliente = (string) ($venta->tipo_documento_cliente ?? '');
        $numDocCliente = (string) ($venta->numero_documento_cliente ?? '');
        $hash = (string) ($venta->hash_cpe ?? '');

        return implode('|', [
            $rucEmisor,
            $tipoDocumento,
            $serie,
            $correlativo,
            $igv,
            $total,
            $fecha,
            $tipoDocCliente,
            $numDocCliente,
            $hash,
        ]);
    }

    protected function resolveSerieCorrelativo(Venta $venta): array
    {
        $serie = (string) ($venta->serie ?? '');
        $correlativo = (string) ($venta->correlativo ?? '');

        if ($serie !== '' && $correlativo !== '') {
            return [$serie, $correlativo];
        }

        $numero = (string) ($venta->numero_comprobante ?? '');
        if (str_contains($numero, '-')) {
            [$serieTmp, $correlativoTmp] = explode('-', $numero, 2);
            return [trim($serieTmp), trim($correlativoTmp)];
        }

        return [$serie, $correlativo];
    }

    protected function buildCuotasPreview(Venta $venta): array
    {
        if (strtolower((string) ($venta->forma_pago ?? 'contado')) !== 'credito') {
            return [];
        }

        $totalCuotas = max((int) ($venta->credito_total_cuotas ?? 1), 1);
        $montoPendiente = (float) ($venta->credito_monto_pendiente ?? 0);
        $fechaBase = optional($venta->credito_fecha_vencimiento);

        if (!$fechaBase) {
            return [[
                'nro' => 1,
                'fecha' => null,
                'monto' => $montoPendiente,
            ]];
        }

        $baseMonto = round($montoPendiente / $totalCuotas, 2);
        $acumulado = 0.0;
        $rows = [];

        for ($i = 1; $i <= $totalCuotas; $i++) {
            $monto = $i === $totalCuotas
                ? round($montoPendiente - $acumulado, 2)
                : $baseMonto;

            $acumulado += $monto;

            $rows[] = [
                'nro' => $i,
                'fecha' => $fechaBase->copy()->addMonths($i - 1),
                'monto' => $monto,
            ];
        }

        return $rows;
    }
}
