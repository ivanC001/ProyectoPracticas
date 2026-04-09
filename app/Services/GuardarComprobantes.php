<?php

namespace App\Services;

use App\Models\GuiasModel\GuiaRemision;
use App\Models\VentasModel\Venta;
use Illuminate\Support\Facades\Storage;

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


    public function guardarPdfEmitido($invoice, string $pdfBinary): string
    {
        $ruta = $this->buildPdfPath(
            (string) $invoice->getTipoDoc(),
            (string) $invoice->getSerie(),
            (string) $invoice->getCorrelativo()
        );

        Storage::disk('local')->put($ruta, $pdfBinary);

        return $ruta;
    }

    public function guardarPdfPorVenta(Venta $venta, string $pdfBinary): string
    {
        $serie = (string) ($venta->serie ?? 'S001');
        $correlativo = (string) ($venta->correlativo ?? '1');
        $tipoDocumento = (string) ($venta->tipo_documento ?? '01');
        $ruta = $this->buildPdfPath($tipoDocumento, $serie, $correlativo);

        Storage::disk('local')->put($ruta, $pdfBinary);

        return $ruta;
    }

    public function guardarPdfPorGuia(GuiaRemision $guia, string $pdfBinary): string
    {
        $serie = (string) ($guia->serie ?? 'T001');
        $correlativo = (string) ($guia->correlativo ?? '1');
        $tipoDocumento = (string) ($guia->tipo_documento ?? '09');
        $ruta = $this->buildPdfPath($tipoDocumento, $serie, $correlativo);

        Storage::disk('local')->put($ruta, $pdfBinary);

        return $ruta;
    }

    protected function buildPdfPath(string $tipoDocumento, string $serie, string $correlativo): string
    {
        $ruc = config('empresa.ruc');
        $nombreArchivo = $ruc . '-' . $tipoDocumento . '-' . $serie . '-' . $correlativo . '.pdf';

        return 'comprobantes/pdf/' . $nombreArchivo;
    }

}
