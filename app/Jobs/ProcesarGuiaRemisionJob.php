<?php

namespace App\Jobs;

use App\Models\GuiasModel\GuiaRemision;
use App\Services\GuardarComprobantes;
use App\Services\GuiaRemisionPdfRenderService;
use App\Services\SunatService;
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

        if ($guia->estado_envio === 'aceptado') {
            return;
        }

        try {
            $sunatService = new SunatService();
            $data = $this->mapearGuia($guia);
            $despatch = $sunatService->getDespatch($data);
            $archivos = new GuardarComprobantes();

            if ($this->hasGreTicketPendiente($guia)) {
                $this->consultarTicketGre($guia, $despatch, $sunatService, $archivos);
                return;
            }

            $guia->update([
                'estado_envio' => 'procesando',
                'mensaje_error' => null,
            ]);

            $api = $sunatService->getGreApi();
            $result = $api->send($despatch);
            $xml = (string) $api->getLastXml();
            $rutaXml = $xml !== '' ? $archivos->guardarXml($despatch, $xml) : $guia->archivo_xml;
            $hash = $xml !== '' ? (new XmlUtils())->getHashSign($xml) : $guia->hash_cpe;

            if (!$result->isSuccess()) {
                $this->marcarGuiaComoError(
                    $guia,
                    (string) optional($result->getError())->getCode(),
                    (string) optional($result->getError())->getMessage(),
                    $rutaXml,
                    $hash
                );
                return;
            }

            $ticket = trim((string) $result->getTicket());
            if ($ticket === '') {
                $this->marcarGuiaComoError(
                    $guia,
                    'GRE',
                    'SUNAT no devolvio ticket para la guia de remision.',
                    $rutaXml,
                    $hash
                );
                return;
            }

            $guia->update([
                'sunat_enviado' => false,
                'fecha_envio_sunat' => now(),
                'estado_envio' => 'procesando',
                'codigo_respuesta_sunat' => $ticket,
                'descripcion_respuesta_sunat' => 'Ticket GRE generado: ' . $ticket,
                'mensaje_error' => null,
                'hash_cpe' => $hash,
                'archivo_xml' => $rutaXml,
            ]);

            $this->consultarTicketGre($guia->fresh(['detalles', 'venta', 'guiaRemitente']), $despatch, $sunatService, $archivos);
        } catch (\Throwable $e) {
            $guia->update([
                'estado_envio' => 'error',
                'mensaje_error' => $this->formatGuiaErrorMessage($e->getMessage()),
            ]);

            throw $e;
        }
    }

    protected function consultarTicketGre(
        GuiaRemision $guia,
        $despatch,
        SunatService $sunatService,
        GuardarComprobantes $archivos
    ): void {
        $ticket = trim((string) $guia->codigo_respuesta_sunat);
        if ($ticket === '') {
            return;
        }

        $api = $sunatService->getGreApi();
        $status = null;

        for ($intento = 0; $intento < 3; $intento++) {
            if ($intento > 0) {
                sleep(2);
            }

            $status = $api->getStatus($ticket);
            if ((string) $status->getCode() !== '98') {
                break;
            }
        }

        if (!$status || (string) $status->getCode() === '98') {
            $guia->update([
                'estado_envio' => 'procesando',
                'descripcion_respuesta_sunat' => 'Ticket GRE en proceso: ' . $ticket,
                'mensaje_error' => null,
            ]);

            self::dispatch($guia->id)->delay(now()->addSeconds(20));
            return;
        }

        $cdrZip = $status->getCdrZip();
        $rutaCdr = $cdrZip ? $archivos->guardarCdr($despatch, $cdrZip) : null;
        $cdr = $status->getCdrResponse();
        $error = $status->getError();

        $aceptado = (string) $status->getCode() === '0' && (!$cdr || $cdr->isAccepted());
        $codigoSunat = $cdr?->getCode() ?: $error?->getCode() ?: (string) $status->getCode();
        $descripcionSunat = $cdr?->getDescription() ?: $error?->getMessage() ?: 'SUNAT no devolvio detalle del ticket GRE.';

        $rutaPdf = null;
        if ($aceptado) {
            $guia->refresh()->loadMissing(['detalles', 'venta', 'guiaRemitente']);
            $pdfBinary = (new GuiaRemisionPdfRenderService())->render($guia);
            $rutaPdf = $archivos->guardarPdfPorGuia($guia, $pdfBinary);
        }

        $guia->update([
            'sunat_enviado' => $aceptado,
            'fecha_envio_sunat' => now(),
            'estado_envio' => $aceptado ? 'aceptado' : 'rechazado',
            'codigo_respuesta_sunat' => $codigoSunat,
            'descripcion_respuesta_sunat' => 'Ticket GRE ' . $ticket . ' | ' . $this->formatGuiaErrorMessage($descripcionSunat),
            'mensaje_error' => $aceptado ? null : $this->formatGuiaErrorMessage($descripcionSunat),
            'archivo_cdr' => $rutaCdr,
            'archivo_pdf' => $rutaPdf,
        ]);
    }

    protected function hasGreTicketPendiente(GuiaRemision $guia): bool
    {
        return (string) $guia->estado_envio === 'procesando'
            && preg_match('/^[A-Za-z0-9\-]{10,}$/', (string) $guia->codigo_respuesta_sunat) === 1;
    }

    protected function marcarGuiaComoError(
        GuiaRemision $guia,
        ?string $codigo,
        ?string $mensaje,
        ?string $rutaXml = null,
        ?string $hash = null
    ): void {
        $guia->update([
            'sunat_enviado' => false,
            'fecha_envio_sunat' => now(),
            'estado_envio' => 'error',
            'codigo_respuesta_sunat' => $codigo ?: 'GRE',
            'descripcion_respuesta_sunat' => $this->formatGuiaErrorMessage((string) $mensaje),
            'mensaje_error' => $this->formatGuiaErrorMessage((string) $mensaje),
            'archivo_xml' => $rutaXml ?: $guia->archivo_xml,
            'hash_cpe' => $hash ?: $guia->hash_cpe,
        ]);
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

    protected function formatGuiaErrorMessage(?string $message): string
    {
        $message = trim((string) $message);

        if ($message === '') {
            return '';
        }

        if (str_contains($message, 'CustomizationID') && str_contains($message, '2112')) {
            return 'SUNAT rechazo la guia porque el XML no estaba en la version compatible con el canal de envio configurado.';
        }

        if (str_contains($message, '1085')) {
            return 'SUNAT exige emitir esta guia por la nueva plataforma GRE. Configura el client_id y client_secret GRE para continuar.';
        }

        if (str_contains($message, 'SUNAT_GRE_CLIENT_ID') || str_contains($message, 'SUNAT_GRE_CLIENT_SECRET')) {
            return 'Faltan configurar las credenciales GRE de SUNAT (client_id y client_secret) para emitir guias.';
        }

        if (str_contains($message, 'Cliente No autorizado')) {
            return 'Cliente no autorizado por GRE. Verifica client_id/client_secret, SUNAT_RUC + usuario SOL + clave SOL, y que el endpoint coincida con tus credenciales (SUNAT o proveedor).';
        }

        if (str_contains($message, 'prefijo "test-"')) {
            return $message;
        }

        if (str_contains($message, 'Faltan SUNAT_RUC')) {
            return 'Faltan credenciales SOL para GRE: SUNAT_RUC, SUNAT_USERNAME y SUNAT_PASSWORD.';
        }

        return preg_replace('/\s+/', ' ', $message) ?: $message;
    }
}
