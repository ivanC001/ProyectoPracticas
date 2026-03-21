<?php

namespace App\Http\Controllers\Factura\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVentaRequest;
use App\Jobs\ProcesarFacturaJob;
class FacturaController extends Controller
{
   public function newventa(StoreVentaRequest $request)
{
    $data = $request->validated();

    // 🔥 ENVÍA A COLA (ASYNC)
    ProcesarFacturaJob::dispatch($data);

    return response()->json([
        'success' => true,
        'mensaje' => 'Factura enviada a procesamiento'
    ]);
}
}