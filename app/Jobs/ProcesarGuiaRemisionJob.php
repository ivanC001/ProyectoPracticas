<?php

namespace App\Jobs;

use App\Models\GuiasModel\GuiaRemision;
use App\Services\GuardarComprobantes;
use App\Services\GuiaRemisionPdfRenderService;
use App\Services\SunatService;
use Greenter\Model\Response\BillResult;
use Greenter\Report\XmlUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcesarGuiaRemisionJob implements ShouldQueue
{
    use Queueable;

    protected int $guiaId;

    public int $tries = 5;
    public array $backoff = [10, 30, 60];

    public function __construct(int $guiaId)
    {
        $this->guiaId = $guiaId;
    }

    public function handle(): void
    {
        $guia = GuiaRemision::with(['detalles', 'venta', 'guiaRemitente'])->findOrFail($this->guiaId);

        if ($guia->sunat_enviado) {
            return;
        }

        $guia->update([
            'estado_envio' => 'procesando',
            'mensaje_error' => null,
        ]);

        try {
            $sunatService = new SunatService();
            $data = $this->mapearGuia($guia);

            $see = $sunatService->getSeeGuia();
            $despatch = $sunatService->getDespatch($data);
            $result = $see->send($despatch);
            /** @var BillResult $result */

            $xml = $see->getFactory()->getLastXml();
            $cdrZip = $result->isSuccess() ? $result->getCdrZip() : null;

            $archivos = new GuardarComprobantes();
            $rutaXml = $archivos->guardarXml($despatch, $xml);
            $rutaCdr = $cdrZip ? $archivos->guardarCdr($despatch, $cdrZip) : null;
            $hash = (new XmlUtils())->getHashSign($xml);

            $sunatResponse = $sunatService->sunatResponse($result);

            $rutaPdf = null;
            if ($sunatResponse['success']) {
                $guia->refresh()->loadMissing(['detalles', 'venta', 'guiaRemitente']);
                $pdfBinary = (new GuiaRemisionPdfRenderService())->render($guia);
                $rutaPdf = $archivos->guardarPdfPorGuia($guia, $pdfBinary);
            }

            $guia->update([
                'sunat_enviado' => true,
                'fecha_envio_sunat' => now(),
                'estado_envio' => $sunatResponse['success'] ? 'aceptado' : 'rechazado',
                'codigo_respuesta_sunat' => $sunatResponse['success']
                    ? ($sunatResponse['cdrRespuesta']['code'] ?? null)
                    : data_get($sunatResponse, 'error.code'),
                'descripcion_respuesta_sunat' => $sunatResponse['success']
                    ? ($sunatResponse['cdrRespuesta']['description'] ?? null)
                    : data_get($sunatResponse, 'error.message'),
                'mensaje_error' => $sunatResponse['success']
                    ? null
                    : (data_get($sunatResponse, 'error.message') ?: 'SUNAT rechazo la guia'),
                'hash_cpe' => $hash,
                'archivo_xml' => $rutaXml,
                'archivo_pdf' => $rutaPdf,
                'archivo_cdr' => $rutaCdr,
            ]);
        } catch (\Throwable $e) {
            $guia->update([
                'estado_envio' => 'error',
                'mensaje_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function mapearGuia(GuiaRemision $guia): array
    {
        $data = [
            'tipo_documento' => $guia->tipo_documento,
            'serie' => $guia->serie,
            'correlativo' => $guia->correlativo,
            'fecha_emision' => optional($guia->fecha_emision)->format('Y-m-d H:i:s'),
            'fecha_traslado' => optional($guia->fecha_traslado)->format('Y-m-d'),
            'motivo_traslado_codigo' => $guia->motivo_traslado_codigo,
            'motivo_traslado_descripcion' => $guia->motivo_traslado_descripcion,
            'modalidad_transporte' => $guia->modalidad_transporte,
            'peso_total' => (float) $guia->peso_total,
            'unidad_peso' => $guia->unidad_peso,
            'numero_bultos' => $guia->numero_bultos,
            'observacion' => $guia->observacion,
            'destinatario' => [
                'tipo_doc' => $guia->destinatario_tipo_doc,
                'num_doc' => $guia->destinatario_num_doc,
                'razon_social' => $guia->destinatario_razon_social,
            ],
            'partida' => [
                'ubigeo' => $guia->partida_ubigeo,
                'direccion' => $guia->partida_direccion,
            ],
            'llegada' => [
                'ubigeo' => $guia->llegada_ubigeo,
                'direccion' => $guia->llegada_direccion,
            ],
            'transportista' => [
                'tipo_doc' => $guia->transportista_tipo_doc,
                'num_doc' => $guia->transportista_num_doc,
                'razon_social' => $guia->transportista_razon_social,
                'reg_mtc' => $guia->transportista_reg_mtc,
            ],
            'conductor' => [
                'tipo_doc' => $guia->conductor_tipo_doc,
                'num_doc' => $guia->conductor_num_doc,
                'nombres' => $guia->conductor_nombres,
                'licencia' => $guia->conductor_licencia,
            ],
            'vehiculo' => [
                'placa' => $guia->vehiculo_placa,
                'secundario_placa' => $guia->vehiculo_secundario_placa,
            ],
            'detalles' => $guia->detalles->map(function ($detalle) {
                return [
                    'codigo' => $detalle->codigo,
                    'descripcion' => $detalle->descripcion,
                    'unidad' => $detalle->unidad,
                    'cantidad' => (float) $detalle->cantidad,
                ];
            })->toArray(),
        ];

        $documentoRelacionado = $this->resolverDocumentoRelacionado($guia);
        if ($documentoRelacionado) {
            $data['documento_relacionado'] = $documentoRelacionado;
        }

        return $data;
    }

    protected function resolverDocumentoRelacionado(GuiaRemision $guia): ?array
    {
        if ($guia->venta) {
            $tipo = (string) $guia->venta->tipo_documento;
            return [
                'tipo' => $tipo,
                'nro' => (string) $guia->venta->numero_comprobante,
                'tipo_desc' => $this->descripcionDoc($tipo),
                'emisor' => (string) ($guia->documento_rel_emisor ?: config('empresa.ruc', '')),
            ];
        }

        if ($guia->guiaRemitente) {
            return [
                'tipo' => '09',
                'nro' => (string) $guia->guiaRemitente->numero_guia,
                'tipo_desc' => $this->descripcionDoc('09'),
                'emisor' => (string) ($guia->documento_rel_emisor ?: config('empresa.ruc', '')),
            ];
        }

        if ($guia->documento_rel_tipo && $guia->documento_rel_numero) {
            $tipo = (string) $guia->documento_rel_tipo;
            return [
                'tipo' => $tipo,
                'nro' => (string) $guia->documento_rel_numero,
                'tipo_desc' => $this->descripcionDoc($tipo),
                'emisor' => (string) ($guia->documento_rel_emisor ?: config('empresa.ruc', '')),
            ];
        }

        return null;
    }

    protected function descripcionDoc(string $tipoDocumento): string
    {
        $catalogo = collect(config('sunat_guia.documentos_relacionados', []))
            ->firstWhere('codigo', $tipoDocumento);

        if ($catalogo) {
            return (string) data_get($catalogo, 'descripcion', 'Comprobante');
        }

        return match ($tipoDocumento) {
            '01' => 'Factura',
            '03' => 'Boleta de Venta',
            '09' => 'Guia de Remision Remitente',
            default => 'Comprobante',
        };
    }
}
