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

class FacturaController extends Controller{
    public function newventa(Request $request){
       

   $envioSunat=new SunatService();
   $see=$envioSunat->getSee();
   $invoice=$envioSunat->getInvoice();

    $result = $see->send($invoice);

    // // Guardar XML firmado digitalmente.
    // file_put_contents($invoice->getName().'.xml',
    //                 $see->getFactory()->getLastXml());

    // Verificamos que la conexión con SUNAT fue exitosa.
    if (!$result->isSuccess()) {
        // Mostrar error al conectarse a SUNAT.
        echo 'Codigo Error: '.$result->getError()->getCode();
        echo 'Mensaje Error: '.$result->getError()->getMessage();
        exit();
    }

    // // Guardamos el CDR
    // file_put_contents('R-'.$invoice->getName().'.zip', $result->getCdrZip());

    $cdr = $result->getCdrResponse();

    $code = (int)$cdr->getCode();

    if ($code === 0) {
        echo 'ESTADO: ACEPTADA'.PHP_EOL;
        if (count($cdr->getNotes()) > 0) {
            echo 'OBSERVACIONES:'.PHP_EOL;
            // Corregir estas observaciones en siguientes emisiones.
            var_dump($cdr->getNotes());
        }  
    } else if ($code >= 2000 && $code <= 3999) {
        echo 'ESTADO: RECHAZADA'.PHP_EOL;
    } else {
        /* Esto no debería darse, pero si ocurre, es un CDR inválido que debería tratarse como un error-excepción. */
        /*code: 0100 a 1999 */
        echo 'Excepción';
    }
    }
}
