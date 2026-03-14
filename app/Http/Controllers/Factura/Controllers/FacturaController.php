<?php

namespace App\Http\Controllers\Factura\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SunatService;
use Greenter\Report\XmlUtils;

class FacturaController extends Controller{
    public function newventa(Request $request){
    $data = $request->all();
    $envioSunat=new SunatService();
    $see=$envioSunat->getSee();
    $invoice=$envioSunat->getInvoice($data);
    // dd($invoice);
    
    $result = $see->send($invoice);
    $response['xml']=$see->getFactory()->getLastXml();
    $response['hash']=(new XmlUtils())->getHashSign($response['xml']);
    $response['sunatResponse']=$envioSunat->sunatResponse($result);
    // $response['Operaciones gravadas'] = $envioSunat->calcularTotales($data['items']);
    return response()->json($response, 200);
    
    }
    public function pdf(Request $request){
        $data = $request->all();
        $envioSunat=new SunatService();
        $invoice=$envioSunat->getInvoice($data);

        return $envioSunat->getHtmlreport($invoice);

    }

}
