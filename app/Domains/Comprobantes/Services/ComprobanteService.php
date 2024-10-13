<?php

namespace App\Domains\Comprobantes\Services;

use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Ws\Services\BillSender;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Ws\Services\SoapClient;
use Greenter\Xml\Builder\InvoiceBuilder;
use Illuminate\Support\Facades\Log;
use App\Domains\Comprobantes\Extenciones\CustomInvoice;

class ComprobanteService
{
    protected $billSender;
    protected $invoiceBuilder;

    public function __construct()
    {
        $ruc = env('SUNAT_RUC');
        $username = env('SUNAT_USERNAME');
        $password = env('SUNAT_PASSWORD');
        $certificatePath = storage_path(env('SUNAT_CERT_PATH'));
        $environment = env('SUNAT_ENV') === 'beta' ? SunatEndpoints::FE_BETA : SunatEndpoints::FE_PRODUCCION;

        if (!file_exists($certificatePath)) {
            throw new \Exception("El archivo del certificado no existe en la ruta: $certificatePath");
        }

        $soapClient = new SoapClient(
            $environment . '?wsdl',
            [
                'trace' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ]
        );

        $soapClient->setCredentials("{$ruc}{$username}", $password);
        

        $this->billSender = new BillSender();
        $this->billSender->setClient($soapClient);

        $this->invoiceBuilder = new InvoiceBuilder();
    }

    public function createAndSendInvoice(array $data)
    {
        try {
            // Datos de la empresa
            $company = new Company();
            $company->setRuc(env('SUNAT_RUC'))
                ->setRazonSocial('Mi Empresa S.A.C.')
                ->setNombreComercial('Mi Comercio')
                ->setAddress((new \Greenter\Model\Company\Address())
                    ->setUbigueo('150101')
                    ->setDepartamento('LIMA')
                    ->setProvincia('LIMA')
                    ->setDistrito('LIMA')
                    ->setDireccion('Av. Ejemplo 123'));

            // Datos del cliente
            $client = new Client();
            $client->setTipoDoc('6')
                ->setNumDoc($data['client_ruc'])
                ->setRznSocial($data['client_name']);

            // Creación de la factura
            $invoice = new Invoice();
            $invoice->setUblVersion('2.1') // Versión del UBL correcta
                //->setCustomizationId('2.0') // Personalización de SUNAT correcta
                ->setTipoOperacion('0101') // Código de operación
                ->setTipoDoc('01') // Código para factura
                ->setSerie('F001')
                ->setCorrelativo('123')
                ->setFechaEmision(new \DateTime())
                ->setTipoMoneda('PEN')
                ->setClient($client)
                ->setCompany($company)
                ->setMtoOperGravadas($data['total_gravadas'])
                ->setMtoIGV($data['total_igv'])
                ->setTotalImpuestos($data['total_igv'])
                ->setValorVenta($data['valor_venta'])
                ->setMtoImpVenta($data['total']);

            // Detalles de la factura
            $details = [];
            foreach ($data['items'] as $item) {
                $detail = new SaleDetail();
                $detail->setCodProducto($item['codigo'])
                    ->setUnidad('NIU')
                    ->setCantidad($item['cantidad'])
                    ->setDescripcion($item['descripcion'])
                    ->setMtoBaseIgv($item['base_igv'])
                    ->setPorcentajeIgv(18)
                    ->setIgv($item['igv'])
                    ->setMtoValorVenta($item['valor_venta'])
                    ->setMtoPrecioUnitario($item['precio_unitario']);
                $details[] = $detail;
            }

            $invoice->setDetails($details);
            $xmlContent = $this->invoiceBuilder->build($invoice);

            // Guardar el archivo XML
            $xmlDirectory = storage_path('app/invoices');
            if (!file_exists($xmlDirectory)) {
                mkdir($xmlDirectory, 0777, true);
            }

            // Crear el nombre del archivo basado en el nombre del ZIP
            $ruc = env('SUNAT_RUC');
            $tipoDoc = '01'; // Tipo de documento (factura)
            $serie = $invoice->getSerie();
            $correlativo = $invoice->getCorrelativo();
            $xmlFilename = "{$ruc}-{$tipoDoc}-{$serie}-{$correlativo}.xml"; // Nombre del archivo XML
            $xmlPath = $xmlDirectory . '/' . $xmlFilename;

            file_put_contents($xmlPath, $xmlContent);

            // Crear el archivo ZIP
            $zipFilename = "{$ruc}-{$tipoDoc}-{$serie}-{$correlativo}.zip";
            $zipPath = $xmlDirectory . '/' . $zipFilename;

            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                $zip->addFile($xmlPath, $xmlFilename);
                $zip->close();
            } else {
                throw new \Exception('No se pudo crear el archivo ZIP.');
            }

            // Enviar a SUNAT
            $filenameWithoutExtension = pathinfo($zipFilename, PATHINFO_FILENAME);
            $result = $this->billSender->send($filenameWithoutExtension, file_get_contents($zipPath));

            if ($result->isSuccess()) {
                $cdrZip = $result->cdrZip ?? null;
                return [
                    'success' => true,
                    'cdr' => $cdrZip,
                    'message' => 'Factura enviada y aceptada correctamente.',
                ];
            } else {
                $error = $result->getError();
                $errorMessage = $error ? $error->getMessage() : 'Error desconocido';

                // Obtener solicitud y respuesta SOAP
                $soapClient = $this->billSender->getClient();
                $soapRequest = $soapClient->__getLastRequest();
                $soapResponse = $soapClient->__getLastResponse();

                // Registrar en el log
                Log::error('Error al enviar la factura a SUNAT: ' . $errorMessage);
                Log::error('Solicitud SOAP: ' . $soapRequest);
                Log::error('Respuesta SOAP: ' . $soapResponse);

                return [
                    'success' => false,
                    'error' => 'Error al enviar la factura a SUNAT: ' . $errorMessage,
                    'soapRequest' => $soapRequest,
                    'soapResponse' => $soapResponse,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar la factura: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Ocurrió un error al enviar la factura: ' . $e->getMessage(),
            ];
        }
    }
}
