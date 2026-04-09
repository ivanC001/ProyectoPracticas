<?php

namespace App\Http\Controllers\Factura;

use App\Http\Controllers\Controller;
use App\Models\GuiasModel\GuiaRemision;
use App\Services\GuardarComprobantes;
use App\Services\GuiaRemisionPdfRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class GuiaRemisionPdfController extends Controller
{
    public function show(Request $request, ?int $id = null)
    {
        $guia = $this->resolveGuia($id ?? $request->integer('guia_id') ?? $request->integer('id'));
        $this->ensureAcceptedGuia($guia);

        $pdfPath = $this->ensurePdfPath($guia);
        if (!$pdfPath) {
            throw ValidationException::withMessages([
                'pdf' => ['El PDF aun no esta disponible para esta guia.'],
            ]);
        }

        return response(Storage::disk('local')->get($pdfPath), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($pdfPath) . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function download(int $id)
    {
        $guia = $this->resolveGuia($id);
        $this->ensureAcceptedGuia($guia);

        $pdfPath = $this->ensurePdfPath($guia);
        if (!$pdfPath) {
            throw ValidationException::withMessages([
                'pdf' => ['El PDF aun no esta disponible para esta guia.'],
            ]);
        }

        return Storage::disk('local')->download($pdfPath, basename($pdfPath), [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function showXml(Request $request, ?int $id = null)
    {
        $guia = $this->resolveGuia($id ?? $request->integer('guia_id') ?? $request->integer('id'));
        $xmlPath = $this->resolveXmlPath($guia);

        if (!$xmlPath) {
            throw ValidationException::withMessages([
                'xml' => ['El XML aun no esta disponible para esta guia.'],
            ]);
        }

        return response(Storage::disk('local')->get($xmlPath), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="' . basename($xmlPath) . '"',
        ]);
    }

    public function downloadXml(int $id)
    {
        $guia = $this->resolveGuia($id);
        $xmlPath = $this->resolveXmlPath($guia);

        if (!$xmlPath) {
            throw ValidationException::withMessages([
                'xml' => ['El XML aun no esta disponible para esta guia.'],
            ]);
        }

        return Storage::disk('local')->download($xmlPath, basename($xmlPath), [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    protected function resolveGuia(?int $id): GuiaRemision
    {
        if (!$id) {
            throw ValidationException::withMessages([
                'guia' => ['Debe indicar la guia que desea visualizar.'],
            ]);
        }

        $guia = GuiaRemision::query()->find($id);
        if (!$guia) {
            throw ValidationException::withMessages([
                'guia' => ['Guia no encontrada.'],
            ]);
        }

        return $guia;
    }

    protected function ensureAcceptedGuia(GuiaRemision $guia): void
    {
        if ($guia->estado_envio !== 'aceptado') {
            throw ValidationException::withMessages([
                'pdf' => ['El PDF solo esta disponible cuando SUNAT acepta la guia.'],
            ]);
        }
    }

    protected function ensurePdfPath(GuiaRemision $guia): ?string
    {
        try {
            $guia->loadMissing(['detalles', 'venta']);
            $pdfBinary = (new GuiaRemisionPdfRenderService())->render($guia);
            $pdfPath = (new GuardarComprobantes())->guardarPdfPorGuia($guia, $pdfBinary);

            if ($guia->archivo_pdf !== $pdfPath) {
                $guia->update([
                    'archivo_pdf' => $pdfPath,
                ]);
            }

            return $pdfPath;
        } catch (\Throwable $e) {
            report($e);
            $path = $guia->archivo_pdf;
            $disk = Storage::disk('local');

            if ($path && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf' && $disk->exists($path)) {
                return $path;
            }

            return null;
        }
    }

    protected function resolveXmlPath(GuiaRemision $guia): ?string
    {
        $path = $guia->archivo_xml;

        if (!$path) {
            return null;
        }

        return Storage::disk('local')->exists($path) ? $path : null;
    }
}

