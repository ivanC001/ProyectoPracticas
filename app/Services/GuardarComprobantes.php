<?php

namespace App\Services;

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
            $invoice->getCorrelativo() . '.html';

        $ruta = 'comprobantes/pdf/' . $nombreArchivo;

        Storage::disk('local')->put($ruta, $html);

        return $ruta;
    }

}