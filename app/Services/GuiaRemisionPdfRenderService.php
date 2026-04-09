<?php

namespace App\Services;

use App\Models\GuiasModel\GuiaRemision;
use Dompdf\Dompdf;
use Dompdf\Options;

class GuiaRemisionPdfRenderService
{
    public function render(GuiaRemision $guia): string
    {
        $guia->loadMissing(['detalles', 'venta']);

        $logoPath = public_path('assets/dist/img/AdminLTELogo.png');
        $logoBase64 = null;

        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
        }

        $html = view('guias.pdf', [
            'guia' => $guia,
            'empresa' => config('empresa'),
            'logoBase64' => $logoBase64,
            'tipoGuiaLabel' => $this->tipoGuiaLabel((string) $guia->tipo_documento),
            'modalidadLabel' => (string) $guia->modalidad_transporte === '01'
                ? 'Transporte publico'
                : 'Transporte privado',
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    public function fileName(GuiaRemision $guia): string
    {
        $number = $guia->numero_guia ?: ($guia->serie . '-' . $guia->correlativo);
        $clean = preg_replace('/[^A-Za-z0-9\-]/', '_', (string) $number);

        return 'guia-remision-' . $clean . '.pdf';
    }

    protected function tipoGuiaLabel(string $tipoDocumento): string
    {
        return match ($tipoDocumento) {
            '09' => 'GUIA DE REMISION REMITENTE',
            '31' => 'GUIA DE REMISION TRANSPORTISTA',
            default => 'GUIA DE REMISION',
        };
    }
}

