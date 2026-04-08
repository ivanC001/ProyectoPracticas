<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\NotasCreditoModel\Nota;
use App\Services\SunatService;

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

        if ($nota->sunat_enviado) return;

        $nota->update(['estado_envio' => 'procesando']);

        try {

            $sunat = new SunatService();

            $data = $this->mapearNota($nota);

            $see = $sunat->getSee();
            $note = $sunat->getNote($data);

            $result = $see->send($note);

            $response = $sunat->sunatResponse($result);

            $nota->update([
                'sunat_enviado' => true,
                'estado_envio' => $response['success'] ? 'aceptado' : 'rechazado',
                'codigo_respuesta_sunat' => $response['cdrRespuesta']['code'] ?? null,
                'descripcion_respuesta_sunat' => $response['cdrRespuesta']['description'] ?? null
            ]);

        } catch (\Throwable $e) {

            $nota->update([
                'estado_envio' => 'error',
                'mensaje_error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private function mapearNota($nota)
    {
        return [
            'tipo_documento' => $nota->tipo_documento,
            'serie' => $nota->serie,
            'correlativo' => $nota->correlativo,
            'fecha_emision' => $nota->fecha_emision,
            'moneda' => 'PEN',

            'tipDocAfectado' => $nota->tipDocAfectado,
            'numDocAfectado' => $nota->numDocAfectado,

            'codMotivo' => $nota->codMotivo,
            'desMotivo' => $nota->desMotivo,

            'cliente' => [
                'tipo_doc' => $nota->venta->tipo_documento_cliente,
                'num_doc' => $nota->venta->numero_documento_cliente,
                'razon_social' => $nota->venta->nombre_cliente
            ],

            'items' => $nota->venta->detalles->map(function ($d) {
                return [
                    'codigo' => $d->codigo_producto,
                    'descripcion' => $d->descripcion,
                    'unidad' => $d->unidad,
                    'cantidad' => $d->cantidad,
                    'valor_unitario' => $d->valor_unitario
                ];
            })->toArray()
        ];
    }
}