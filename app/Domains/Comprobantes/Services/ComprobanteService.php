<?php

namespace App\Domains\Comprobantes\Services;
use App\Domains\Comprobantes\Services\GenerarPDF;
use App\Domains\Comprobantes\Config\CertificadoConfig;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Client\Client;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Company\Company;
use Illuminate\Support\Facades\Storage;

use Exception;

class ComprobanteService
{
    protected $certificadoConfig;
    protected $generarPDF;

    public function __construct(CertificadoConfig $certificadoConfig ,GenerarPDF $generarPDF)
    {
        $this->certificadoConfig = $certificadoConfig;
        $this->generarPDF = $generarPDF;
    }

    public function emitir(array $data)
    {
        try {
            // Configuración de la conexión a SUNAT
            $see = $this->certificadoConfig->getSee();

            // Asignación de tipo de comprobante, serie y moneda
            $tipoComprobante = $data['tipo_comprobante'];
            $serie = $tipoComprobante === '01' ? 'F001' : 'B001';
            $moneda = $data['factura']['moneda'];

            // Obtener el siguiente correlativo de la serie
            $correlativo = $this->obtenerSiguienteCorrelativo($tipoComprobante, $serie);

            // Configuración del cliente
            $cliente = new Client();
            $cliente->setTipoDoc($data['cliente']['tipo_doc'])
                ->setNumDoc($data['cliente']['num_doc'] ?? '-')
                ->setRznSocial($data['cliente']['razon_social'] ?? '');

            $detalles = [];
            $mtoGravadas = 0;
            $mtoIgv = 0;

            foreach ($data['detalle'] as $item) {
                $valorVenta = $item['valor_unitario'] * $item['cantidad'];
                $igv = $valorVenta * 0.18;
                $precioUnitarioConIgv = $item['valor_unitario'] * 1.18;

                $detalle = new SaleDetail();
                $detalle->setUnidad($item['unidad'])
                    ->setCantidad($item['cantidad'])
                    ->setCodProducto($item['codigo'])
                    ->setDescripcion($item['descripcion'])
                    ->setMtoValorUnitario($item['valor_unitario'])
                    ->setPorcentajeIgv(18)
                    ->setMtoPrecioUnitario($precioUnitarioConIgv)
                    ->setMtoValorVenta($valorVenta)
                    ->setTotalImpuestos($igv);

                $detalles[] = $detalle;

                $mtoGravadas += $valorVenta;
                $mtoIgv += $igv;
            }

            $mtoTotal = $mtoGravadas + $mtoIgv;

            // Configuración de la compañía
            $company = new Company();
            $company->setRuc(config('empresa.ruc'))
                ->setNombreComercial(config('empresa.nombre_comercial'))
                ->setRazonSocial(config('empresa.razon_social'));

            // Creación del comprobante
            $documento = new Invoice();
            $documento->setTipoDoc($tipoComprobante)
                ->setSerie($serie)
                ->setCorrelativo($correlativo)
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

            // Generar el XML del comprobante
            $xmlContent = $see->getXmlSigned($documento);
            $xmlFileName = "{$serie}-{$correlativo}.xml";
            Storage::put("comprobantes/xml/{$xmlFileName}", $xmlContent); // Guardar en una ruta privada

            // Enviar el comprobante a SUNAT
            $result = $see->send($documento);

            // Registrar la venta en la base de datos
            $venta = \App\Domains\Comprobantes\Models\Venta::create([
                'tipo_documento' => $tipoComprobante,
                'serie' => $serie,
                'correlativo' => $correlativo,
                'fecha_emision' => now(),
                'moneda' => $moneda,
                'tipo_documento_cliente' => $data['cliente']['tipo_doc'],
                'numero_documento_cliente' => $data['cliente']['num_doc'],
                'nombre_cliente' => $data['cliente']['razon_social'],
                'total_venta' => $mtoTotal,
                'total_impuestos' => $mtoIgv,
                'estado_envio' => 'pendiente',
                'archivo_xml' => "comprobantes/xml/{$xmlFileName}", // Ruta privada
            ]);

            // Registrar cada detalle de la venta
            foreach ($data['detalle'] as $item) {
                \App\Domains\Comprobantes\Models\DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'codigo_producto' => $item['codigo'],
                    'descripcion' => $item['descripcion'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['valor_unitario'],
                    'subtotal' => $item['valor_unitario'] * $item['cantidad'],
                    'igv' => $item['valor_unitario'] * $item['cantidad'] * 0.18,
                    'total' => $item['valor_unitario'] * $item['cantidad'] * 1.18,
                ]);
            }

            if ($result->isSuccess()) {
                if ($result instanceof \Greenter\Model\Response\BillResult) {
                    $cdr = $result->getCdrResponse();
                    $cdrZipContent = $result->getCdrZip();
                    $cdrZipFileName = "{$serie}-{$correlativo}.zip";
                    Storage::put("comprobantes/cdr/{$cdrZipFileName}", $cdrZipContent); // Guardar en una ruta privada
                   
                    $pdfFileName = $this->generarPDF->generarPDFConQR($venta, $data['detalle']);
                    $venta->update(['archivo_pdf' => $pdfFileName]);
                    
                    // Actualizar el estado de la venta y guardar la respuesta de SUNAT
                    $venta->update([
                        'estado_envio' => 'aceptado',
                        'archivo_xml' => "comprobantes/xml/{$xmlFileName}",
                        'archivo_pdf' => "comprobantes/cdr/{$cdrZipFileName}"
                    ]);

                    $venta->update(['archivo_pdf' => $pdfFileName]);

                    \App\Domains\Comprobantes\Models\RespuestaSunat::create([
                        'venta_id' => $venta->id,
                        'codigo_respuesta' => $cdr->getCode(),
                        'mensaje_respuesta' => $cdr->getDescription(),
                        'respuesta_completa' => json_encode($cdrZipContent)
                    ]);

                    return [
                        'success' => true,
                        'cdr' => $cdr->getDescription()
                    ];
                }
            } else {
                $error = $result->getError();
                $venta->update(['estado_envio' => 'rechazado']);
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



    protected function obtenerSiguienteCorrelativo($tipoComprobante, $serie)
    {
        // Consultar el último correlativo de la serie en la tabla `ventas`
        $ultimaVenta = \App\Domains\Comprobantes\Models\Venta::where('tipo_documento', $tipoComprobante)
            ->where('serie', $serie)
            ->orderBy('correlativo', 'desc')
            ->first();

        // Si no hay registros, el correlativo empieza en 1
        if (!$ultimaVenta) {
            return 1;
        }

        // Incrementar el último correlativo en 1
        return $ultimaVenta->correlativo + 1;
    }
}
