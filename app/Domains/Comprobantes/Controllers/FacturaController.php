<?php

namespace App\Domains\Comprobantes\Controllers;

use App\Domains\Comprobantes\Services\GreenterService;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Client\Client;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Company\Company; // Asegúrate de importar la clase Company
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class FacturaController extends Controller
{
    public function emitirFactura()
    {
        try {
            // Obtener instancia de Greenter
            $see = GreenterService::getSee();
            if (!$see) {
                return response()->json(['success' => false, 'message' => 'Error al obtener la instancia de See.']);
            }

            // Crear el cliente (receptor de la factura)
            $cliente = new Client();
            $cliente->setTipoDoc('6') // RUC
                ->setNumDoc('20512345678') // RUC del cliente
                ->setRznSocial('Cliente Ejemplo S.A.C.');

            if (!$cliente || !$cliente->getNumDoc()) {
                return response()->json(['success' => false, 'message' => 'El cliente no se ha creado correctamente o no tiene RUC.']);
            }

            // Crear los detalles de la factura
            $detalle = new SaleDetail();
            $detalle->setUnidad('NIU') // Unidad de medida
                ->setCantidad(2)
                ->setCodProducto('P001')
                ->setCodProdSunat('10000000')
                ->setDescripcion('Producto A')
                ->setMtoValorUnitario(100.00)
                ->setPorcentajeIgv(18.00)
                ->setIgv(36.00)
                ->setTipAfeIgv('10')
                ->setMtoPrecioUnitario(118.00)
                ->setMtoValorVenta(200.00)
                ->setTotalImpuestos(36.00);

            // Crear la compañía (emisor de la factura)
            $company = new Company();
            $company->setRuc('20123456789') // RUC de la empresa emisora
                ->setNombreComercial('Mi Empresa S.A.C.')
                ->setRazonSocial('Mi Empresa S.A.C.');

            if (!$company || !$company->getRuc()) {
                return response()->json(['success' => false, 'message' => 'La compañía no se ha creado correctamente o no tiene RUC.']);
            }

            // Crear la factura
            $factura = new Invoice();
            $factura->setTipoDoc('01') // Factura (01)
                ->setSerie('F001')
                ->setCorrelativo('123')
                ->setFechaEmision(new \DateTime())
                ->setTipoMoneda('PEN')
                ->setClient($cliente)
                ->setCompany($company) // Asigna la compañía
                ->setMtoOperGravadas(100.00)
                ->setMtoIgv(18.00)
                ->setTotalImpuestos(18.00)
                ->setValorVenta(100.00)
                ->setMtoImpVenta(118.00)
                ->setDetails([$detalle]);

            // Enviar la factura a SUNAT, Greenter firma el XML automáticamente
            $result = $see->send($factura);

            // Depuración para ver el resultado
            // dd($result); // Puedes usar dd() para ver el resultado durante las pruebas

            if ($result->isSuccess()) {
                if ($result instanceof \Greenter\Model\Response\BillResult) {
                    $cdr = $result->getCdrResponse();
                    return response()->json([
                        'success' => true,
                        'cdr' => $cdr->getDescription(),
                        'cdr_zip' => base64_encode($result->getCdrZip())
                    ]);
                }
            } else {
                // Mostrar el error si ocurrió
                $error = $result->getError();
                return response()->json([
                    'success' => false,
                    'message' => $error->getMessage(),
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al emitir la factura: ' . $e->getMessage(),
            ]);
        }
    }
}
