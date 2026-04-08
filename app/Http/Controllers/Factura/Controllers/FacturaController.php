<?php

namespace App\Http\Controllers\Factura\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVentaRequest;
use App\Services\VentaService;
use App\Jobs\ProcesarFacturaJob;
class FacturaController extends Controller
{
   public function newventa(StoreVentaRequest $request)
{
    
    $data = $request->validated();

    $ventaService = new VentaService();

    // 🔥 1. GUARDAR PRIMERO
    $venta = $ventaService->guardarVentaPendiente($data);

    // 🔥 2. ENVIAR A COLA
    ProcesarFacturaJob::dispatch($venta->id);

    return response()->json([
        'success' => true,
        'mensaje' => 'Factura registrada y en proceso',
        'venta_id' => $venta->id
    ]);
}
}