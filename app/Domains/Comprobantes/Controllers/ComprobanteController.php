<?php

namespace App\Domains\Comprobantes\Controllers;

use App\Domains\Comprobantes\Services\ComprobanteService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ComprobanteController extends Controller
{
    protected $comprobanteService;

    public function __construct(ComprobanteService $comprobanteService)
    {
        $this->comprobanteService = $comprobanteService;
    }

    public function create(Request $request)
    {
        $data = $request->all();

        $response = $this->comprobanteService->createAndSendInvoice($data);

        return response()->json($response, $response['success'] ? 200 : 500);
    }
}
