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
use Greenter\Report\HtmlReport;
use Greenter\Report\Resolver\DefaultTemplateResolver;
use Luecano\NumeroALetras\NumeroALetras;
use App\Models\VentasModel\SerieCorrelativo;

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
    // public function getInvoice($data){
    //      $details = $this->getDetails($data['items']);

    //     $totales = $this->calcularTotales($data['items']);
    //         // Venta
    //     $invoice = (new Invoice())
    //         ->setUblVersion($data['ublVersion'] ?? '2.1')
    //         ->setTipoOperacion($data['0101'] ?? null) // Venta - Catalog. 51
    //         ->setTipoDoc($data['01'] ?? null) // Factura - Catalog. 01 
    //         ->setSerie($data['serie'] ?? null)
    //         ->setCorrelativo($data['correlativo'] ?? null)
    //         ->setFechaEmision(new DateTime($data['fecha_emision']) ?? null)
    //         ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
    //         ->setTipoMoneda($data['moneda'] ?? null) // Sol - Catalog. 02
    //         ->setCompany($this->getCompany())
    //         ->setClient($this->getClient($data['cliente']))

    //         //monto operaciones grabadass
    //         ->setMtoOperGravadas($totales['gravadas'])
    //         ->setMtoIGV($totales['igv'])
    //         ->setTotalImpuestos($totales['igv'])
    //         ->setValorVenta($totales['gravadas'])
    //         ->setSubTotal($totales['total'])
    //         ->setMtoImpVenta($totales['total'])
    //         ->setDetails($details)
    //         ->setLegends($this->getLegend($totales['total']));
    //         ;
    //     return $invoice;
    // }
   
    public function getInvoice($data)
    {
        // obtener serie y correlativo desde BD
        $serieData = $this->obtenerSerieCorrelativo($data['tipo_documento']);

        $serie = $serieData['serie'];
        $correlativo = $serieData['correlativo'];

        // generar detalles
        $details = $this->getDetails($data['items']);

        // calcular totales
        $totales = $this->calcularTotales($data['items']);

        $invoice = (new Invoice())

            ->setUblVersion('2.1')

            ->setTipoOperacion('0101')

            ->setTipoDoc($data['tipo_documento']) // 01 factura | 03 boleta

            ->setSerie($serie)

            ->setCorrelativo($correlativo)

            ->setFechaEmision(new DateTime($data['fecha_emision']))

            ->setFormaPago(new FormaPagoContado())

            ->setTipoMoneda($data['moneda'])

            ->setCompany($this->getCompany())

            ->setClient($this->getClient($data['cliente']))

            ->setMtoOperGravadas($totales['gravadas'])

            ->setMtoIGV($totales['igv'])

            ->setTotalImpuestos($totales['igv'])

            ->setValorVenta($totales['gravadas'])

            ->setSubTotal($totales['total'])

            ->setMtoImpVenta($totales['total'])

            ->setDetails($details)

            ->setLegends($this->getLegend($totales['total'], $data['moneda']));

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
            ->setTipoDoc($cliente['tipo_doc']?? null)
            ->setNumDoc($cliente['num_doc']?? null)
            ->setRznSocial($cliente['razon_social']?? null);
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
            $valorVenta = round($item['cantidad'] * $item['valor_unitario'], 2);
            $igv = round($valorVenta * 0.18, 2);
            $precioUnitario = round(($valorVenta + $igv) / $item['cantidad'], 2);
            $detail = (new SaleDetail())
                ->setCodProducto($item['codigo']?? null)
                ->setUnidad($item['unidad'] ?? 'NIU') // Unidad - Catalog. 03
                ->setCantidad($item['cantidad']?? null)
                ->setMtoValorUnitario($item['valor_unitario']?? null)
                ->setDescripcion($item['descripcion']?? null)
                ->setMtoBaseIgv($valorVenta)
                ->setPorcentajeIgv(18.00) // 18%
                ->setIgv($igv)
                ->setTipAfeIgv('10') // Gravado Op. Onerosa - Catalog. 07 (puede ser dinamico)
                ->setTotalImpuestos($igv) // Suma de impuestos en el detalle
                ->setMtoValorVenta($valorVenta)
                ->setMtoPrecioUnitario($precioUnitario);
            $details[] = $detail;

        }

            return  $details;
    }
    public function getLegend($total, $moneda){
            $formatee = new NumeroALetras();

            $monedaTexto = 'SOLES';

            if ($moneda === 'USD') {
                $monedaTexto = 'DOLARES AMERICANOS';
            }

            $legend = (new Legend())
                ->setCode('1000')
                ->setValue('SON ' . $formatee->toInvoice($total, 2, $monedaTexto));

            return [$legend];

    }
    // public function sunatResponse($result){
        
    //     $respuesta['success']=$result->isSuccess();

    // // // Guardar XML firmado digitalmente.
    // // file_put_contents($invoice->getName().'.xml',
    // //                 $see->getFactory()->getLastXml());

    //     // Verificamos que la conexión con SUNAT fue exitosa.
    //     if (!$respuesta['success']) {
    //         // Mostrar error al conectarse a SUNAT.
    //         $respuesta['Error']=[
    //             'code' => $result->getError()->getCode(),
    //             'message' => $result->getError()->getMessage()
    //         ];
    //         return $respuesta;
    //     }

    //     // // Guardamos el CDR
    //     // file_put_contents('R-'.$invoice->getName().'.zip', $result->getCdrZip());
    //     $respuesta['cdrZip']= base64_encode($result->getCdrZip());

    //     $cdr = $result->getCdrResponse();
    //     $respuesta['cdrRespuesta']=[
    //         'code' => (int)$cdr->getCode(),
    //         'description' => $cdr->getDescription(),
    //         'notes' => $cdr->getNotes()
    //     ];
    //     return $respuesta;

    // }
    public function sunatResponse($result)
    {
        $respuesta = [];

        $respuesta['success'] = $result->isSuccess();

        // Si hubo error de conexión con SUNAT
        if (!$respuesta['success']) {

            $respuesta['error'] = [
                'code' => $result->getError()->getCode(),
                'message' => $result->getError()->getMessage()
            ];

            return $respuesta;
        }

        // Obtener respuesta del CDR
        $cdr = $result->getCdrResponse();

        $respuesta['cdrRespuesta'] = [
            'code' => (int) $cdr->getCode(),
            'description' => $cdr->getDescription(),
            'notes' => $cdr->getNotes()
        ];

        return $respuesta;
    }
    public function calcularTotales($items)
    {
        $gravadas = 0;
        $exoneradas = 0;
        $inafectas = 0;
        $igv = 0;

        foreach ($items as $item) {

            $monto = $item['cantidad'] * $item['valor_unitario'];
            $tipAfeIgv = $item['tipAfeIgv'] ?? '10';

            if ($tipAfeIgv == '10') { // Gravado
                $gravadas += $monto;
                $igv += $monto * 0.18;
            }

            if ($tipAfeIgv == '20') { // Exonerado
                $exoneradas += $monto;
            }

            if ($tipAfeIgv == '30') { // Inafecto
                $inafectas += $monto;
            }
        }

        $total = round($gravadas + $exoneradas + $inafectas + $igv, 2);

        return [
            'gravadas' => round($gravadas, 2),
            'exoneradas' => round($exoneradas, 2),
            'inafectas' => round($inafectas, 2),
            'igv' => round($igv, 2),
            'total' => round($total, 2)
        ];
    }
    //respuesta sunat y reporte
    public function getHtmlreport($invoice){
        $report=new HtmlReport();
        $resolver=new DefaultTemplateResolver();
        $report->setTemplate($resolver->getTemplate($invoice));
        $params = [
            'system' => [
                'logo' => file_get_contents(public_path('assets/dist/img/AdminLTELogo.png')),
                'hash' => 'qqnr2dN4p/HmaEA/CJuVGo7dv5g=', // Valor Resumen 
            ],
            'user' => [
                'header'     => 'Telf: <b>(01) 123375</b>', // Texto que se ubica debajo de la dirección de empresa
                'extras'     => [
                    // Leyendas adicionales
                    ['name' => 'CONDICION DE PAGO', 'value' => 'Efectivo'     ],
                    ['name' => 'VENDEDOR'         , 'value' => 'GITHUB SELLER'],
                ],
                'footer' => '<p>Nro Resolucion: <b>3232323</b></p>'
            ]
        ];

       return $report->render($invoice, $params);

    }
    public function obtenerSerieCorrelativo($tipoDocumento)
    {
        $serieData = SerieCorrelativo::obtenerSiguienteCorrelativo($tipoDocumento);

        return [
            'serie' => $serieData['serie'],
            'correlativo' => $serieData['correlativo']
        ];
    }





}