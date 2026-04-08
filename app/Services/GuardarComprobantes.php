<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use App\Services\SunatService;

class GuardarComprobantes
{

    /*
    |--------------------------------------------------------------------------
    | Guardar XML firmado
    |--------------------------------------------------------------------------
    */

    public function guardarXml($invoice, $xml)
    {

        $ruc = config('empresa.ruc');

        $nombreArchivo = $ruc . '-' .
            $invoice->getTipoDoc() . '-' .
            $invoice->getSerie() . '-' .
            $invoice->getCorrelativo() . '.xml';

        $ruta = 'comprobantes/xml/' . $nombreArchivo;

        Storage::disk('local')->put($ruta, $xml);

        return $ruta;
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar CDR
    |--------------------------------------------------------------------------
    */

    public function guardarCdr($invoice, $cdrZip)
    {

        $ruc = config('empresa.ruc');

        $nombreArchivo = 'R-' .
            $ruc . '-' .
            $invoice->getTipoDoc() . '-' .
            $invoice->getSerie() . '-' .
            $invoice->getCorrelativo() . '.zip';

        $ruta = 'comprobantes/cdr/' . $nombreArchivo;

        Storage::disk('local')->put($ruta, $cdrZip);

        return $ruta;
    }


    /*
    |--------------------------------------------------------------------------
    | Generar PDF (HTML de Greenter)
    |--------------------------------------------------------------------------
    */

    public function generarPdf($invoice)
    {

        $sunatService = new SunatService();

        $html = $sunatService->getHtmlreport($invoice);

        $ruc = config('empresa.ruc');

        $nombreArchivo = $ruc . '-' .
            $invoice->getTipoDoc() . '-' .
            $invoice->getSerie() . '-' .
            $invoice->getCorrelativo() . '.pdf';

        $ruta = 'comprobantes/pdf/' . $nombreArchivo;

        $pdfBinary = $this->renderPdf($html);

        Storage::disk('local')->put($ruta, $pdfBinary);

        return $ruta;
    }

    public function guardarPdfDesdeHtml(string $sourcePath, string $html): string
    {
        $pdfPath = preg_replace('/\.html?$/i', '.pdf', $sourcePath);

        if (!$pdfPath) {
            $pdfPath = $sourcePath . '.pdf';
        }

        $pdfBinary = $this->renderPdf($html);

        Storage::disk('local')->put($pdfPath, $pdfBinary);

        return $pdfPath;
    }

    protected function renderPdf(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

}
