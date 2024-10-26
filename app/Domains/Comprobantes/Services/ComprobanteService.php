<?php

namespace App\Domains\Comprobantes\Services;

use App\Domains\Comprobantes\Config\CertificadoConfig;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Client\Client;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Company\Company;
use Exception;

class ComprobanteService
{
    protected $certificadoConfig;

    public function __construct(CertificadoConfig $certificadoConfig)
    {
        $this->certificadoConfig = $certificadoConfig;
    }

    public function emitir(array $data)
    {
        try {
            // Configuración de la conexión a SUNAT
            $see = $this->certificadoConfig->getSee();
    
            // Asignación de tipo de comprobante y moneda
            $tipoComprobante = $data['tipo_comprobante'];
            $moneda = $data['factura']['moneda'];
    
            // Configuración del cliente
            $cliente = new Client();
            $cliente->setTipoDoc($data['cliente']['tipo_doc'])
                ->setNumDoc($data['cliente']['num_doc'] ?? '-')  
                ->setRznSocial($data['cliente']['razon_social'] ?? '');
    
            // Crear detalles y calcular totales
            $detalles = [];
            $mtoGravadas = 0;
            $mtoIgv = 0;
    
            foreach ($data['detalle'] as $item) {
                // Calcular valor de venta a partir del valor unitario y la cantidad
                $valorVenta = $item['valor_unitario'] * $item['cantidad'];
                $igv = $valorVenta * 0.18;  // Calcular IGV como 18% del valor de venta
                $precioUnitarioConIgv = $item['valor_unitario'] * 1.18;
    
                $detalle = new SaleDetail();
                $detalle->setUnidad($item['unidad'])
                    ->setCantidad($item['cantidad'])
                    ->setCodProducto($item['codigo'])
                    ->setDescripcion($item['descripcion'])
                    ->setMtoValorUnitario($item['valor_unitario'])
                    ->setPorcentajeIgv(18)  // Fijo al 18%
                    ->setMtoPrecioUnitario($precioUnitarioConIgv)  // Precio con IGV incluido
                    ->setMtoValorVenta($valorVenta)  // Valor venta calculado
                    ->setTotalImpuestos($igv);  // IGV calculado
    
                $detalles[] = $detalle;
    
                // Sumar valores para los totales generales
                $mtoGravadas += $valorVenta;
                $mtoIgv += $igv;
            }
    
            // Calcular el monto total (gravadadas + IGV)
            $mtoTotal = $mtoGravadas + $mtoIgv;
    
            // Configuración de la compañía
            $company = new Company();
            $company->setRuc(config('empresa.ruc'))
                ->setNombreComercial(config('empresa.nombre_comercial'))
                ->setRazonSocial(config('empresa.razon_social'));
    
            // Creación del comprobante
            $documento = new Invoice();
            $documento->setTipoDoc($tipoComprobante)
                ->setSerie($data['factura']['serie'])
                ->setCorrelativo($data['factura']['correlativo'])
                ->setFechaEmision(new \DateTime())
                ->setTipoMoneda($moneda)
                ->setClient($cliente)
                ->setCompany($company)
                ->setMtoOperGravadas($mtoGravadas)
                ->setMtoIgv($mtoIgv)
                ->setTotalImpuestos($mtoIgv)
                ->setValorVenta($mtoGravadas)
                ->setMtoImpVenta($mtoTotal)
                ->setDetails($detalles);
    
            // Enviar el comprobante a SUNAT
            $result = $see->send($documento);
    
            if ($result->isSuccess()) {
                if ($result instanceof \Greenter\Model\Response\BillResult) {
                    $cdr = $result->getCdrResponse();
                    return [
                        'success' => true,
                        'cdr' => $cdr->getDescription(),
                        'cdr_zip' => base64_encode($result->getCdrZip())
                    ];
                }
            } else {
                $error = $result->getError();
                return [
                    'success' => false,
                    'message' => $error->getMessage()
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
}
