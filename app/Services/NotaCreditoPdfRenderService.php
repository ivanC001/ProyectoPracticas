<?php

namespace App\Services;

use App\Models\NotasCreditoModel\Nota;
use Dompdf\Dompdf;
use Dompdf\Options;
use Luecano\NumeroALetras\NumeroALetras;

class NotaCreditoPdfRenderService
{
    public function render(Nota $nota): string
    {
        $nota->loadMissing('venta.detalles');

        $venta = $nota->venta;
        $items = $venta->detalles;
        $igvCatalogService = new SunatIgvCatalogService();
        $factor = $this->resolveNotaFactor($nota);

        $itemsForTotals = $items->map(function ($detalle) use ($factor) {
            return [
                'cantidad' => (float) $detalle->cantidad,
                'valor_unitario' => round((float) $detalle->valor_unitario * $factor, 6),
                'descuento' => round((float) $detalle->descuento * $factor, 6),
                'tip_afe_igv' => $detalle->tip_afe_igv,
            ];
        })->toArray();

        $itemsDisplay = $items->map(function ($detalle, $idx) use ($igvCatalogService, $factor) {
            $item = [
                'cantidad' => (float) $detalle->cantidad,
                'valor_unitario' => round((float) $detalle->valor_unitario * $factor, 6),
                'descuento' => round((float) $detalle->descuento * $factor, 6),
                'tip_afe_igv' => $detalle->tip_afe_igv,
            ];
            $line = $igvCatalogService->calculateLine($item);

            return [
                'index' => $idx + 1,
                'codigo' => $detalle->codigo_producto,
                'descripcion' => $detalle->descripcion,
                'unidad' => $detalle->unidad,
                'cantidad' => (float) $detalle->cantidad,
                'valor_unitario' => (float) $item['valor_unitario'],
                'subtotal' => (float) ($line['subtotal'] ?? 0),
                'igv' => (float) ($line['igv'] ?? 0),
                'total' => (float) ($line['total'] ?? 0),
            ];
        })->values();

        $totales = $igvCatalogService->calculateTotals($itemsForTotals);
        $total = (float) ($nota->total ?? $totales['total']);

        $formatter = new NumeroALetras();
        $moneda = strtoupper((string) ($venta->moneda ?? 'PEN'));
        $monedaTexto = $moneda === 'USD' ? 'DOLARES AMERICANOS' : 'SOLES';
        $monedaSimbolo = $moneda === 'USD' ? 'US$' : 'S/';

        $logoPath = public_path('assets/dist/img/AdminLTELogo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
        }

        $html = view('NotasCredito.pdf', [
            'nota' => $nota,
            'venta' => $venta,
            'empresa' => config('empresa'),
            'logoBase64' => $logoBase64,
            'tipoNotaLabel' => $this->tipoNotaLabel($nota->tipo_documento),
            'moneda' => $moneda,
            'monedaSimbolo' => $monedaSimbolo,
            'itemsDisplay' => $itemsDisplay,
            'totales' => $totales,
            'total' => $total,
            'totalLetras' => $formatter->toInvoice($total, 2, $monedaTexto),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    protected function tipoNotaLabel(?string $tipoDocumento): string
    {
        return match ((string) $tipoDocumento) {
            '07' => 'NOTA DE CREDITO ELECTRONICA',
            '08' => 'NOTA DE DEBITO ELECTRONICA',
            default => 'NOTA ELECTRONICA',
        };
    }

    protected function resolveNotaFactor(Nota $nota): float
    {
        $montoVenta = max((float) ($nota->venta->total_venta ?? 0), 0.0);
        $montoNota = max((float) ($nota->total ?? 0), 0.0);

        if ($montoVenta <= 0 || $montoNota <= 0) {
            return 1.0;
        }

        return $montoNota / $montoVenta;
    }
}
