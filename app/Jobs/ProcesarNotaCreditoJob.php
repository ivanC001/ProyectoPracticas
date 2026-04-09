<?php

namespace App\Jobs;

use App\Models\NotasCreditoModel\Nota;
use App\Services\GuardarComprobantes;
use App\Services\NotaCreditoPdfRenderService;
use App\Services\SunatService;
use Greenter\Model\Response\BillResult;
use Greenter\Report\XmlUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcesarNotaCreditoJob implements ShouldQueue
{
    use Queueable;

    protected $notaId;

    public function __construct($notaId)
    {
        $this->notaId = $notaId;
    }

    public function handle(): void
    {
        $nota = Nota::with('venta.detalles')->findOrFail($this->notaId);

        if ($nota->sunat_enviado) {
            return;
        }

        $nota->update(['estado_envio' => 'procesando']);

        try {
            $sunat = new SunatService();
            $data = $this->mapearNota($nota);

            $see = $sunat->getSee();
            $note = $sunat->getNote($data);
            $result = $see->send($note);
            /** @var BillResult $result */

            $xml = $see->getFactory()->getLastXml();
            $cdrZip = $result->isSuccess() ? $result->getCdrZip() : null;

            $archivos = new GuardarComprobantes();
            $rutaXml = $archivos->guardarXml($note, $xml);
            $rutaCdr = $cdrZip ? $archivos->guardarCdr($note, $cdrZip) : null;
            $hash = (new XmlUtils())->getHashSign($xml);

            $response = $sunat->sunatResponse($result);

            $rutaPdf = null;
            if ($response['success']) {
                $nota->refresh()->load('venta.detalles');
                $pdfBinary = (new NotaCreditoPdfRenderService())->render($nota);
                $rutaPdf = $archivos->guardarPdfEmitido($note, $pdfBinary);
            }

            $nota->update([
                'sunat_enviado' => true,
                'fecha_envio_sunat' => now(),
                'estado_envio' => $response['success'] ? 'aceptado' : 'rechazado',
                'codigo_respuesta_sunat' => $response['success']
                    ? ($response['cdrRespuesta']['code'] ?? null)
                    : ($response['error']['code'] ?? null),
                'descripcion_respuesta_sunat' => $response['success']
                    ? ($response['cdrRespuesta']['description'] ?? null)
                    : ($response['error']['message'] ?? null),
                'hash_cpe' => $hash,
                'archivo_xml' => $rutaXml,
                'archivo_pdf' => $rutaPdf,
                'archivo_cdr' => $rutaCdr,
            ]);
        } catch (\Throwable $e) {
            $nota->update([
                'estado_envio' => 'error',
                'mensaje_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function mapearNota($nota): array
    {
        return [
            'tipo_documento' => $nota->tipo_documento,
            'serie' => $nota->serie,
            'correlativo' => $nota->correlativo,
            'fecha_emision' => $nota->fecha_emision,
            'moneda' => $nota->venta->moneda ?? 'PEN',
            'tipDocAfectado' => $nota->tipDocAfectado,
            'numDocAfectado' => $nota->numDocAfectado,
            'codMotivo' => $nota->codMotivo,
            'desMotivo' => $nota->desMotivo,
            'cliente' => [
                'tipo_doc' => $nota->venta->tipo_documento_cliente,
                'num_doc' => $nota->venta->numero_documento_cliente,
                'razon_social' => $nota->venta->nombre_cliente,
            ],
            'items' => $nota->venta->detalles->map(function ($d) {
                return [
                    'codigo' => $d->codigo_producto,
                    'descripcion' => $d->descripcion,
                    'unidad' => $d->unidad,
                    'cantidad' => $d->cantidad,
                    'valor_unitario' => $d->valor_unitario,
                    'descuento' => $d->descuento,
                    'tip_afe_igv' => $d->tip_afe_igv,
                ];
            })->toArray(),
        ];
    }
}
