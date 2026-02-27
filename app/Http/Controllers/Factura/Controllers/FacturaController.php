<?php

namespace App\Http\Controllers\Factura\Controllers;
use DateTime;
use Greenter\See;
use Illuminate\Http\Request;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\SaleDetail;
use App\Http\Controllers\Controller;
use App\Services\SunatService;
use Illuminate\Support\Facades\Storage;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Report\XmlUtils;

class FacturaController extends Controller{
    public function newventa(Request $request){
    $data = $request->all();
    $envioSunat=new SunatService();
    $see=$envioSunat->getSee();
    $invoice=$envioSunat->getInvoice($data);
    

    $result = $see->send($invoice);
    $response['xml']=$see->getFactory()->getLastXml();
    $response['hash']=(new XmlUtils())->getHashSign($response['xml']);
    $response['sunatResponse']=$envioSunat->sunatResponse($result);
    return response()->json($response, 200);
    }
}
