<?php

namespace App\Services;

use DateTime;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;

use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;

use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;

use Greenter\Report\HtmlReport;
use Greenter\Report\Resolver\DefaultTemplateResolver;

use Luecano\NumeroALetras\NumeroALetras;

class SunatService
{
    /*
    |-----------------------------------------
    | CONEXIÓN SUNAT
    |-----------------------------------------
    */
    public function getSee()
    {
        $certPath = config('empresa.sunat_cert_path');
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

    /*
    |-----------------------------------------
    | FACTURA / BOLETA
    |-----------------------------------------
    */
    public function getInvoice($data)
    {
        if (!isset($data['serie']) || !isset($data['correlativo'])) {
            throw new \Exception("Serie y correlativo son obligatorios");
        }

        $details = $this->getDetails($data['items']);
        $totales = $this->calcularTotales($data['items']);

        return (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($data['tipo_documento'])

            // 🔥 DESDE BD
            ->setSerie($data['serie'])
            ->setCorrelativo($data['correlativo'])

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
    }

    /*
    |-----------------------------------------
    | NOTA DE CRÉDITO / DÉBITO
    |-----------------------------------------
    */
    public function getNote($data)
    {
        if (!isset($data['serie']) || !isset($data['correlativo'])) {
            throw new \Exception("Serie y correlativo son obligatorios");
        }

        $details = $this->getDetails($data['items']);
        $totales = $this->calcularTotales($data['items']);

        return (new Note())
            ->setUblVersion('2.1')
            ->setTipoDoc($data['tipo_documento']) // 07 o 08

            ->setSerie($data['serie'])
            ->setCorrelativo($data['correlativo'])

            ->setFechaEmision(new DateTime($data['fecha_emision']))

            // documento afectado
            ->setTipDocAfectado($data['tipDocAfectado'])
            ->setNumDocfectado($data['numDocAfectado'])
            // ✔ OK($data['numDocAfectado'])
            // motivo
            ->setCodMotivo($data['codMotivo'])
            ->setDesMotivo($data['desMotivo'])

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
    }

    /*
    |-----------------------------------------
    | EMPRESA
    |-----------------------------------------
    */
    public function getCompany()
    {
        return (new Company())
            ->setRuc(config('empresa.ruc'))
            ->setRazonSocial(config('empresa.razon_social'))
            ->setNombreComercial(config('empresa.nombre_comercial'))
            ->setAddress($this->getAddress());
    }

    /*
    |-----------------------------------------
    | CLIENTE
    |-----------------------------------------
    */
    public function getClient($cliente)
    {
        return (new Client())
            ->setTipoDoc($cliente['tipo_doc'] ?? null)
            ->setNumDoc($cliente['num_doc'] ?? null)
            ->setRznSocial($cliente['razon_social'] ?? null);
    }

    /*
    |-----------------------------------------
    | DIRECCIÓN
    |-----------------------------------------
    */
    public function getAddress()
    {
        return (new Address())
            ->setUbigueo(config('empresa.ubigeo'))
            ->setDepartamento(config('empresa.departamento'))
            ->setProvincia(config('empresa.provincia'))
            ->setDistrito(config('empresa.distrito'))
            ->setUrbanizacion(config('empresa.urbanizacion'))
            ->setDireccion(config('empresa.direccion'))
            ->setCodLocal(config('empresa.cod_local'));
    }

    /*
    |-----------------------------------------
    | DETALLES
    |-----------------------------------------
    */
    public function getDetails($items)
    {
        $details = [];

        foreach ($items as $item) {

            $valorVenta = round($item['cantidad'] * $item['valor_unitario'], 2);
            $igv = round($valorVenta * 0.18, 2);
            $precioUnitario = round(($valorVenta + $igv) / $item['cantidad'], 2);

            $details[] = (new SaleDetail())
                ->setCodProducto($item['codigo'])
                ->setUnidad($item['unidad'] ?? 'NIU')
                ->setCantidad($item['cantidad'])
                ->setMtoValorUnitario($item['valor_unitario'])
                ->setDescripcion($item['descripcion'])

                ->setMtoBaseIgv($valorVenta)
                ->setPorcentajeIgv(18)
                ->setIgv($igv)

                ->setTipAfeIgv('10')
                ->setTotalImpuestos($igv)

                ->setMtoValorVenta($valorVenta)
                ->setMtoPrecioUnitario($precioUnitario);
        }

        return $details;
    }

    /*
    |-----------------------------------------
    | TOTALES
    |-----------------------------------------
    */
    public function calcularTotales($items)
    {
        $gravadas = 0;
        $igv = 0;

        foreach ($items as $item) {

            $monto = $item['cantidad'] * $item['valor_unitario'];
            $gravadas += $monto;
            $igv += $monto * 0.18;
        }

        return [
            'gravadas' => round($gravadas, 2),
            'igv' => round($igv, 2),
            'total' => round($gravadas + $igv, 2)
        ];
    }

    /*
    |-----------------------------------------
    | LEYENDA
    |-----------------------------------------
    */
    public function getLegend($total, $moneda)
    {
        $formatter = new NumeroALetras();

        $monedaTexto = $moneda === 'USD'
            ? 'DOLARES AMERICANOS'
            : 'SOLES';

        return [
            (new Legend())
                ->setCode('1000')
                ->setValue('SON ' . $formatter->toInvoice($total, 2, $monedaTexto))
        ];
    }

    /*
    |-----------------------------------------
    | RESPUESTA SUNAT
    |-----------------------------------------
    */
    public function sunatResponse($result)
    {
        if (!$result->isSuccess()) {
            return [
                'success' => false,
                'error' => [
                    'code' => $result->getError()->getCode(),
                    'message' => $result->getError()->getMessage()
                ]
            ];
        }

        $cdr = $result->getCdrResponse();

        return [
            'success' => true,
            'cdrRespuesta' => [
                'code' => (int) $cdr->getCode(),
                'description' => $cdr->getDescription(),
                'notes' => $cdr->getNotes()
            ]
        ];
    }

    /*
    |-----------------------------------------
    | HTML PDF
    |-----------------------------------------
    */
    public function getHtmlreport($invoice)
    {
        $report = new HtmlReport();
        $resolver = new DefaultTemplateResolver();

        $report->setTemplate($resolver->getTemplate($invoice));

        $params = [
            'system' => [
                'logo' => file_get_contents(public_path('assets/dist/img/AdminLTELogo.png')),
                'hash' => 'HASH_PLACEHOLDER',
            ],
        ];

        return $report->render($invoice, $params);
    }
}