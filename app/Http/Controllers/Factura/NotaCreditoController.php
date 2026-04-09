<?php

namespace App\Http\Controllers\Factura;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotaRequest;
use App\Jobs\ProcesarNotaCreditoJob;
use App\Models\NotasCreditoModel\Nota;
use App\Models\VentasModel\Venta;
use App\Services\GuardarComprobantes;
use App\Services\NotaCreditoPdfRenderService;
use App\Services\NotaCreditoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class NotaCreditoController extends Controller
{
    public function index()
    {
        $notas = Nota::query()
            ->with('venta:id,numero_comprobante,nombre_cliente')
            ->orderByDesc('id')
            ->get([
                'id',
                'venta_id',
                'tipo_documento',
                'serie',
                'correlativo',
                'numero_comprobante',
                'codMotivo',
                'desMotivo',
                'estado_envio',
                'codigo_respuesta_sunat',
                'descripcion_respuesta_sunat',
                'mensaje_error',
                'archivo_pdf',
                'archivo_xml',
                'created_at',
            ]);

        return response()->json([
            'data' => $notas->map(function (Nota $nota) {
                return [
                    'id' => $nota->id,
                    'venta_id' => $nota->venta_id,
                    'tipo_documento' => $nota->tipo_documento,
                    'serie' => $nota->serie,
                    'correlativo' => $nota->correlativo,
                    'numero_comprobante' => $nota->numero_comprobante,
                    'codMotivo' => $nota->codMotivo,
                    'desMotivo' => $nota->desMotivo,
                    'estado_envio' => $nota->estado_envio,
                    'codigo_respuesta_sunat' => $nota->codigo_respuesta_sunat,
                    'descripcion_respuesta_sunat' => $nota->descripcion_respuesta_sunat,
                    'mensaje_error' => $nota->mensaje_error,
                    'archivo_pdf' => $nota->archivo_pdf,
                    'archivo_xml' => $nota->archivo_xml,
                    'created_at' => $nota->created_at,
                    'factura_afectada' => $nota->venta?->numero_comprobante,
                    'cliente' => $nota->venta?->nombre_cliente,
                ];
            }),
        ]);
    }

    public function store(StoreNotaRequest $request)
    {
        $data = $request->validated();

        $service = new NotaCreditoService();
        $nota = $service->guardarNotaPendiente($data);

        ProcesarNotaCreditoJob::dispatch($nota->id);

        return response()->json([
            'success' => true,
            'mensaje' => 'Nota enviada a procesamiento',
            'nota_id' => $nota->id,
        ]);
    }

    public function facturasEmitidas(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = Venta::query()
            ->where('estado_envio', 'aceptado')
            ->whereIn('tipo_documento', ['01', '03'])
            ->orderByDesc('id')
            ->select([
                'id',
                'numero_comprobante',
                'tipo_documento',
                'fecha_emision',
                'nombre_cliente',
                'numero_documento_cliente',
                'total_venta',
                'moneda',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('numero_comprobante', 'like', "%{$search}%")
                    ->orWhere('nombre_cliente', 'like', "%{$search}%")
                    ->orWhere('numero_documento_cliente', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'data' => $query->limit(20)->get(),
        ]);
    }

    public function showPdf(Request $request, ?int $id = null)
    {
        $nota = $this->resolveNota($id ?? $request->integer('nota_id') ?? $request->integer('id'));
        $pdfPath = $this->ensurePdfPath($nota);

        if (!$pdfPath) {
            throw ValidationException::withMessages([
                'pdf' => ['El PDF aun no esta disponible para esta nota.'],
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

    public function downloadPdf(int $id)
    {
        $nota = $this->resolveNota($id);
        $pdfPath = $this->ensurePdfPath($nota);

        if (!$pdfPath) {
            throw ValidationException::withMessages([
                'pdf' => ['El PDF aun no esta disponible para esta nota.'],
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
        $nota = $this->resolveNota($id ?? $request->integer('nota_id') ?? $request->integer('id'));
        $xmlPath = $this->resolveXmlPath($nota);

        if (!$xmlPath) {
            throw ValidationException::withMessages([
                'xml' => ['El XML aun no esta disponible para esta nota.'],
            ]);
        }

        return response(Storage::disk('local')->get($xmlPath), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="' . basename($xmlPath) . '"',
        ]);
    }

    public function downloadXml(int $id)
    {
        $nota = $this->resolveNota($id);
        $xmlPath = $this->resolveXmlPath($nota);

        if (!$xmlPath) {
            throw ValidationException::withMessages([
                'xml' => ['El XML aun no esta disponible para esta nota.'],
            ]);
        }

        return Storage::disk('local')->download($xmlPath, basename($xmlPath), [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    protected function resolveNota(?int $id): Nota
    {
        if (!$id) {
            throw ValidationException::withMessages([
                'nota' => ['Debe indicar la nota que desea visualizar.'],
            ]);
        }

        $nota = Nota::query()->find($id);
        if (!$nota) {
            throw ValidationException::withMessages([
                'nota' => ['Nota no encontrada.'],
            ]);
        }

        return $nota;
    }

    protected function ensurePdfPath(Nota $nota): ?string
    {
        $path = $nota->archivo_pdf;
        $disk = Storage::disk('local');

        if ($path && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf' && $disk->exists($path)) {
            return $path;
        }

        if ($nota->estado_envio !== 'aceptado') {
            return null;
        }

        try {
            $nota->loadMissing('venta.detalles');
            $pdfBinary = (new NotaCreditoPdfRenderService())->render($nota);
            $pdfPath = (new GuardarComprobantes())->guardarPdfEmitido(
                new class($nota)
                {
                    public function __construct(private Nota $nota)
                    {
                    }

                    public function getTipoDoc(): string
                    {
                        return (string) $this->nota->tipo_documento;
                    }

                    public function getSerie(): string
                    {
                        return (string) $this->nota->serie;
                    }

                    public function getCorrelativo(): string
                    {
                        return (string) $this->nota->correlativo;
                    }
                },
                $pdfBinary
            );

            $nota->update(['archivo_pdf' => $pdfPath]);

            return $pdfPath;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    protected function resolveXmlPath(Nota $nota): ?string
    {
        $path = $nota->archivo_xml;
        if (!$path) {
            return null;
        }

        return Storage::disk('local')->exists($path) ? $path : null;
    }
}
