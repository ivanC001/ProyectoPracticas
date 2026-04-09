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

        $itemsForTotals = $items->map(function ($detalle) {
            return [
                'cantidad' => (float) $detalle->cantidad,
                'valor_unitario' => (float) $detalle->valor_unitario,
                'descuento' => (float) $detalle->descuento,
                'tip_afe_igv' => $detalle->tip_afe_igv,
            ];
        })->toArray();

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
}
