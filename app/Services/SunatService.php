<?php
namespace App\Services;

use DateTime;
use Greenter\See;
use Greenter\Model\Sale\Invoice;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\SaleDetail;
use App\Http\Controllers\Controller;

class SunatService
{
    public function getSee(){

        
        $certPath = config('empresa.sunat_cert_path');
        $certPass = config('empresa.sunat_cert_pass');
        $certificate = file_get_contents($certPath);
        $see = new See();
        $see->setCertificate($certificate);

        $see->setService(SunatEndpoints::FE_BETA);

        $see->setClaveSOL(
            config('empresa.sunat_ruc'),
            config('empresa.sunat_username'),
            config('empresa.sunat_password')
        );
        return $see;
        
 
    }
    public function getInvoice($data){
         $details = $this->getDetails($data['items']);

        $totales = $this->calcularTotales($data['items']);
            // Venta
        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta - Catalog. 51
            ->setTipoDoc('01') // Factura - Catalog. 01 
            ->setSerie($data['serie'])
            ->setCorrelativo($data['correlativo'])
            ->setFechaEmision(new DateTime($data['fecha_emision']))
            ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
            ->setTipoMoneda($data['moneda']) // Sol - Catalog. 02
            ->setCompany($this->getCompany())
            ->setClient($this->getClient($data['cliente']))
            ->setMtoOperGravadas($totales['gravadas'])
            ->setMtoIGV($totales['igv'])
            ->setTotalImpuestos($totales['igv'])
            ->setValorVenta($totales['gravadas'])
            ->setSubTotal($totales['total'])
            ->setMtoImpVenta($totales['total'])
            ->setDetails($details)
            ->setLegends($this->getLegend($totales['total']));
            ;
        return $invoice;
    }

    public function getCompany(){
        $company = (new Company())
            ->setRuc(config('empresa.ruc'))
            ->setRazonSocial(config('empresa.razon_social'))
            ->setNombreComercial(config('empresa.nombre_comercial'))
            ->setAddress($this->getAddress());
        return $company;

    }
    public function getClient($cliente){         
    // Cliente
        $client = (new Client())
            ->setTipoDoc($cliente['tipo_doc'])
            ->setNumDoc($cliente['num_doc'])
            ->setRznSocial($cliente['razon_social']);
        return $client;
    }
    public function getAddress(){
          // Emisor
        $address = (new Address())
            ->setUbigueo(config('empresa.ubigeo'))
            ->setDepartamento(config('empresa.departamento'))
            ->setProvincia(config('empresa.provincia'))
            ->setDistrito(config('empresa.distrito'))
            ->setUrbanizacion(config('empresa.urbanizacion'))
            ->setDireccion(config('empresa.direccion'))
            ->setCodLocal(config('empresa.cod_local')); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.
        return $address;
        
    }
    public function getDetails($items){

        $details=[];
        foreach ($items as $item) {
            $valorVenta = $item['cantidad'] * $item['valor_unitario'];
            $igv = $valorVenta * 0.18;
            $precioUnitario = ($valorVenta + $igv) / $item['cantidad'];
            $detail = (new SaleDetail())
                ->setCodProducto($item['codigo'])
                ->setUnidad('NIU') // Unidad - Catalog. 03
                ->setCantidad($item['cantidad'])
                ->setMtoValorUnitario($item['valor_unitario'])
                ->setDescripcion($item['descripcion'])
                ->setMtoBaseIgv($valorVenta)
                ->setPorcentajeIgv(18.00) // 18%
                ->setIgv($igv)
                ->setTipAfeIgv('10') // Gravado Op. Onerosa - Catalog. 07
                ->setTotalImpuestos($igv) // Suma de impuestos en el detalle
                ->setMtoValorVenta($valorVenta)
                ->setMtoPrecioUnitario($precioUnitario);
            $details[] = $detail;

        }

            return  $details;
    }
    public function getLegend(){
          $legend = (new Legend())
            ->setCode('1000') // Monto en letras - Catalog. 52
            ->setValue('SON DOSCIENTOS TREINTA Y SEIS CON 00/100 SOLES');
        return [$legend];

    }
    public function sunatResponse($result){
        
        $respuesta['success']=$result->isSuccess();

    // // Guardar XML firmado digitalmente.
    // file_put_contents($invoice->getName().'.xml',
    //                 $see->getFactory()->getLastXml());

        // Verificamos que la conexión con SUNAT fue exitosa.
        if (!$respuesta['success']) {
            // Mostrar error al conectarse a SUNAT.
            $respuesta['Error']=[
                'code' => $result->getError()->getCode(),
                'message' => $result->getError()->getMessage()
            ];
            return $respuesta;
        }

        // // Guardamos el CDR
        // file_put_contents('R-'.$invoice->getName().'.zip', $result->getCdrZip());
        $respuesta['cdrZip']= base64_encode($result->getCdrZip());

        $cdr = $result->getCdrResponse();
        $respuesta['cdrRespuesta']=[
            'code' => (int)$cdr->getCode(),
            'description' => $cdr->getDescription(),
            'notes' => $cdr->getNotes()
        ];
        return $respuesta;

    }
    public function calcularTotales($items)
    {
        $gravadas = 0;

        foreach ($items as $item) {
            $gravadas += $item['cantidad'] * $item['valor_unitario'];
        }

        $igv = $gravadas * 0.18;
        $total = $gravadas + $igv;

        return [
            'gravadas' => round($gravadas, 2),
            'igv' => round($igv, 2),
            'total' => round($total, 2)
        ];
    }
}