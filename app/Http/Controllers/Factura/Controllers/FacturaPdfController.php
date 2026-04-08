<?php

namespace App\Http\Controllers\Factura\Controllers;

use App\Http\Controllers\Controller;
use App\Models\VentasModel\Venta;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Luecano\NumeroALetras\NumeroALetras;

class FacturaPdfController extends Controller
{
    public function show(Request $request, ?int $id = null)
    {
        $venta = $this->resolveVenta($id ?? $request->integer('venta_id') ?? $request->integer('id'));
        $pdfBinary = $this->buildFacturaPdfBinary($venta);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $this->getPdfFileName($venta) . '"',
        ]);
    }

    public function download(int $id)
    {
        $venta = $this->resolveVenta($id);
        $pdfBinary = $this->buildFacturaPdfBinary($venta);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->getPdfFileName($venta) . '"',
        ]);
    }

    protected function resolveVenta(?int $id): Venta
    {
        if (!$id) {
            throw ValidationException::withMessages([
                'venta' => ['Debe indicar la factura que desea visualizar.'],
            ]);
        }

        $venta = Venta::with('detalles')->find($id);

        if (!$venta) {
            throw ValidationException::withMessages([
                'venta' => ['Factura no encontrada.'],
            ]);
        }

        return $venta;
    }

    protected function buildFacturaPdfBinary(Venta $venta): string
    {
        $subtotal = (float) $venta->detalles->sum('subtotal');
        $igv = (float) $venta->detalles->sum('igv');
        $total = (float) $venta->total_venta;

        if ($subtotal <= 0) {
            $subtotal = max($total - (float) $venta->total_impuestos, 0);
        }

        if ($igv <= 0) {
            $igv = (float) $venta->total_impuestos;
        }

        if ($total <= 0) {
            $total = $subtotal + $igv;
        }

        $formatter = new NumeroALetras();
        $monedaTexto = strtoupper((string) $venta->moneda) === 'USD'
            ? 'DOLARES AMERICANOS'
            : 'SOLES';

        $logoPath = public_path('assets/dist/img/AdminLTELogo.png');
        $logoBase64 = null;

        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
        }

        $estadoColorMap = [
            'aceptado' => '#15803d',
            'rechazado' => '#b91c1c',
            'error' => '#b91c1c',
            'procesando' => '#1d4ed8',
            'pendiente' => '#92400e',
        ];

        $estadoLabelMap = [
            'aceptado' => 'ACEPTADO',
            'rechazado' => 'RECHAZADO',
            'error' => 'ERROR',
            'procesando' => 'PROCESANDO',
            'pendiente' => 'PENDIENTE',
        ];

        $html = view('factura.pdf', [
            'venta' => $venta,
            'empresa' => config('empresa'),
            'logoBase64' => $logoBase64,
            'tipoDocumentoLabel' => $this->tipoDocumentoLabel($venta->tipo_documento),
            'monedaSimbolo' => strtoupper((string) $venta->moneda) === 'USD' ? 'US$' : 'S/',
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $total,
            'totalLetras' => $formatter->toInvoice($total, 2, $monedaTexto),
            'estadoColor' => $estadoColorMap[$venta->estado_envio] ?? '#64748b',
            'estadoLabel' => $estadoLabelMap[$venta->estado_envio] ?? strtoupper((string) $venta->estado_envio),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    protected function getPdfFileName(Venta $venta): string
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
}
