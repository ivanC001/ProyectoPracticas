<?php

namespace App\Http\Controllers\Factura;

use App\Http\Controllers\Controller;
use App\Jobs\ProcesarNotaCreditoJob;
use App\Models\NotasCreditoModel\Nota;
use App\Services\NotaCreditoService;
use Illuminate\Http\Request;

class NotaCreditoController extends Controller
{
    public function index()
    {
        $notas = Nota::query()
            ->orderByDesc('id')
            ->get([
                'id',
                'tipo_documento',
                'serie',
                'correlativo',
                'numero_comprobante',
                'desMotivo',
                'estado_envio',
            ]);

        return response()->json(['data' => $notas]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $service = new NotaCreditoService();
        $nota = $service->guardarNotaPendiente($data);

        ProcesarNotaCreditoJob::dispatch($nota->id);

        return response()->json([
            'success' => true,
            'mensaje' => 'Nota enviada a procesamiento',
            'nota_id' => $nota->id,
        ]);
    }
}
