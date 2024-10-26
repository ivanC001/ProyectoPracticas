<?php

namespace App\Domains\Comprobantes\Controllers;

use App\Domains\Comprobantes\Services\ComprobanteService;
use App\Domains\Comprobantes\Requests\EmitirFacturaRequest;  // Form Request para validación
use App\Http\Controllers\Controller;
use Exception;

class ComprobanteController extends Controller
{
    protected $comprobanteService;

    public function __construct(ComprobanteService $comprobanteService)
    {
        $this->comprobanteService = $comprobanteService;
    }

    public function emitirFactura(EmitirFacturaRequest $request)
    {
        try {
            
            $result = $this->comprobanteService->emitir($request->validated());

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al emitir la factura: ' . $e->getMessage(),
            ], 500);
        }
    }
}
