<?php

namespace App\Services;

use DateTime;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Despatch\Vehicle;
use Greenter\Model\Sale\Document;
use Greenter\Model\Sale\Cuota;
use Greenter\Model\Sale\Detraction;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\FormaPagos\FormaPagoCredito;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Api as SunatGreApi;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Luecano\NumeroALetras\NumeroALetras;

class SunatService
{
    public function getSee()
    {
        return $this->buildSee(
            $this->resolveEnv() === 'produccion'
                ? SunatEndpoints::FE_PRODUCCION
                : SunatEndpoints::FE_BETA
        );
    }

    public function getSeeGuia()
    {
        return $this->buildSee(
            $this->resolveEnv() === 'produccion'
                ? SunatEndpoints::GUIA_PRODUCCION
                : SunatEndpoints::GUIA_BETA
        );
    }

    public function getGreApi(): SunatGreApi
    {
        // Fallback a env() para evitar que un worker con config stale deje "vacias"
        // las credenciales GRE aunque ya existan en .env.
        $clientId = trim((string) (
            config('empresa.sunat_gre_client_id')
            ?: env('SUNAT_GRE_CLIENT_ID', '')
        ));
        $clientSecret = trim((string) (
            config('empresa.sunat_gre_client_secret')
            ?: env('SUNAT_GRE_CLIENT_SECRET', '')
        ));

        if ($clientId === '' || $clientSecret === '') {
            throw new \RuntimeException(
                'Faltan configurar SUNAT_GRE_CLIENT_ID y SUNAT_GRE_CLIENT_SECRET para emitir guias por la plataforma GRE.'
            );
        }

        $certPath = (string) config('empresa.sunat_cert_path');
        $certificate = file_get_contents($certPath);

        $baseUrl = trim((string) (
            config('empresa.sunat_gre_base_url')
            ?: env('SUNAT_GRE_BASE_URL', '')
        ));

        $authUrl = trim((string) config('empresa.sunat_gre_auth_url', 'https://api-seguridad.sunat.gob.pe/v1'));
        $cpeUrl = trim((string) config('empresa.sunat_gre_cpe_url', 'https://api-cpe.sunat.gob.pe/v1'));

        if ($baseUrl !== '') {
            $authUrl = rtrim($baseUrl, '/');
            $cpeUrl = rtrim($baseUrl, '/');
        }

        $sunatRuc = trim((string) (
            config('empresa.sunat_ruc')
            ?: env('SUNAT_RUC', '')
        ));
        $sunatUsername = trim((string) (
            config('empresa.sunat_username')
            ?: env('SUNAT_USERNAME', '')
        ));
        $sunatPassword = trim((string) (
            config('empresa.sunat_password')
            ?: env('SUNAT_PASSWORD', '')
        ));

        if ($sunatRuc === '' || $sunatUsername === '' || $sunatPassword === '') {
            throw new \RuntimeException(
                'Faltan SUNAT_RUC, SUNAT_USERNAME o SUNAT_PASSWORD para autenticar GRE.'
            );
        }

        $api = new SunatGreApi([
            'auth' => $authUrl,
            'cpe' => $cpeUrl,
        ]);

        $api->setCertificate($certificate);
        $api->setApiCredentials($clientId, $clientSecret);
        $api->setClaveSOL(
            $sunatRuc,
            $sunatUsername,
            $sunatPassword
        );

        return $api;
    }

    public function getInvoice($data)
    {
        if (!isset($data['serie']) || !isset($data['correlativo'])) {
            throw new \Exception('Serie y correlativo son obligatorios');
        }

        $igvCatalogService = new SunatIgvCatalogService();
        $details = $this->getDetails($data['items']);
        $totales = $igvCatalogService->calculateTotals($data['items']);

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion((string) data_get($data, 'tipo_operacion', '0101'))
            ->setTipoDoc($data['tipo_documento'])
            ->setSerie($data['serie'])
            ->setCorrelativo($data['correlativo'])
            ->setFechaEmision(new DateTime($data['fecha_emision']))
            ->setTipoMoneda($data['moneda'])
            ->setCompany($this->getCompany())
            ->setClient($this->getClient($data['cliente']))
            ->setMtoOperGravadas($totales['gravadas'])
            ->setMtoOperExoneradas($totales['exoneradas'])
            ->setMtoOperInafectas($totales['inafectas'])
            ->setMtoOperExportacion($totales['exportacion'])
            ->setMtoOperGratuitas($totales['gratuitas'])
            ->setMtoIGVGratuitas($totales['igv_gratuitas'])
            ->setMtoIGV($totales['igv'])
            ->setTotalImpuestos($totales['total_impuestos'])
            ->setValorVenta($totales['valor_venta'])
            ->setSubTotal($totales['sub_total'])
            ->setMtoImpVenta($totales['total'])
            ->setDetails($details)
            ->setLegends($this->getLegend($totales, $data['moneda']))
            ->setFormaPago($this->buildFormaPago($data, $totales));

        $cuotas = $this->buildCuotas($data, $totales);
        if (!empty($cuotas)) {
            $invoice->setCuotas($cuotas);
        }

        $fechaVencimiento = data_get($data, 'credito.fecha_vencimiento');
        if ($fechaVencimiento) {
            $invoice->setFecVencimiento(new DateTime((string) $fechaVencimiento));
        }

        $detraccion = $this->buildDetraccion($data);
        if ($detraccion) {
            $invoice->setDetraccion($detraccion);
        }

        $observacion = trim((string) data_get($data, 'observacion', ''));
        if ($observacion !== '') {
            $invoice->setObservacion($observacion);
        }

        return $invoice;
    }

    public function getDespatch(array $data): Despatch
    {
        if (!isset($data['serie']) || !isset($data['correlativo'])) {
            throw new \Exception('Serie y correlativo son obligatorios para guia de remision');
        }

        $partida = (new Direction(
            (string) data_get($data, 'partida.ubigeo', (string) config('empresa.ubigeo', '')),
            (string) data_get($data, 'partida.direccion', (string) config('empresa.direccion', ''))
        ))
            ->setCodLocal((string) config('empresa.cod_local', '0000'))
            ->setRuc((string) config('empresa.ruc', ''));

        $llegada = new Direction(
            (string) data_get($data, 'llegada.ubigeo', (string) config('empresa.ubigeo', '')),
            (string) data_get($data, 'llegada.direccion', '')
        );

        $shipment = (new Shipment())
            ->setModTraslado((string) data_get($data, 'modalidad_transporte', '02'))
            ->setCodTraslado((string) data_get($data, 'motivo_traslado_codigo', '01'))
            ->setDesTraslado((string) data_get($data, 'motivo_traslado_descripcion', 'VENTA'))
            ->setFecTraslado(new DateTime((string) data_get($data, 'fecha_traslado')))
            ->setPesoTotal((float) data_get($data, 'peso_total', 0))
            ->setUndPesoTotal((string) data_get($data, 'unidad_peso', 'KGM'))
            ->setPartida($partida)
            ->setLlegada($llegada);

        $numBultos = (int) data_get($data, 'numero_bultos', 0);
        if ($numBultos > 0) {
            $shipment->setNumBultos($numBultos);
        }

        if ((string) data_get($data, 'modalidad_transporte') === '01') {
            $transportista = (new Transportist())
                ->setTipoDoc((string) data_get($data, 'transportista.tipo_doc', '6'))
                ->setNumDoc((string) data_get($data, 'transportista.num_doc', ''))
                ->setRznSocial((string) data_get($data, 'transportista.razon_social', ''))
                ->setNroMtc((string) data_get($data, 'transportista.reg_mtc', ''))
                ->setPlaca((string) data_get($data, 'vehiculo.placa', ''))
                ->setChoferTipoDoc((string) data_get($data, 'conductor.tipo_doc', '1'))
                ->setChoferDoc((string) data_get($data, 'conductor.num_doc', ''));

            $shipment->setTransportista($transportista);
        }

        if ((string) data_get($data, 'modalidad_transporte') === '02') {
            $nombresConductor = trim((string) data_get($data, 'conductor.nombres', ''));
            [$nombres, $apellidos] = $this->splitDriverName($nombresConductor);

            $driver = (new Driver())
                ->setTipo('Principal')
                ->setTipoDoc((string) data_get($data, 'conductor.tipo_doc', '1'))
                ->setNroDoc((string) data_get($data, 'conductor.num_doc', ''))
                ->setNombres($nombres)
                ->setApellidos($apellidos)
                ->setLicencia((string) data_get($data, 'conductor.licencia', ''));

            $vehicle = (new Vehicle())
                ->setPlaca((string) data_get($data, 'vehiculo.placa', ''));

            $secondaryPlate = trim((string) data_get($data, 'vehiculo.secundario_placa', ''));
            if ($secondaryPlate !== '') {
                $vehicle->setSecundarios([
                    (new Vehicle())->setPlaca($secondaryPlate),
                ]);
            }

            $shipment->setChoferes([$driver])->setVehiculo($vehicle);
        }

        $despatch = (new Despatch())
            ->setTipoDoc((string) data_get($data, 'tipo_documento', '09'))
            ->setSerie((string) data_get($data, 'serie'))
            ->setCorrelativo((string) data_get($data, 'correlativo'))
            ->setFechaEmision(new DateTime((string) data_get($data, 'fecha_emision')))
            ->setCompany($this->getCompany())
            ->setDestinatario($this->getClient((array) data_get($data, 'destinatario', [])))
            ->setEnvio($shipment);

        $observacion = trim((string) data_get($data, 'observacion', ''));
        if ($observacion !== '') {
            $despatch->setObservacion($observacion);
        }

        $tipoDocRelacionado = (string) data_get($data, 'documento_relacionado.tipo', '');
        $nroDocRelacionado = trim((string) data_get($data, 'documento_relacionado.nro', ''));

        if ($tipoDocRelacionado !== '' && $nroDocRelacionado !== '') {
            $despatch->setRelDoc(
                (new Document())
                    ->setTipoDoc($tipoDocRelacionado)
                    ->setNroDoc($nroDocRelacionado)
            );
        }

        $detalles = [];

        foreach ((array) data_get($data, 'detalles', []) as $item) {
            $detalles[] = (new DespatchDetail())
                ->setCantidad((float) data_get($item, 'cantidad', 0))
                ->setUnidad((string) data_get($item, 'unidad', 'NIU'))
                ->setDescripcion((string) data_get($item, 'descripcion', ''))
                ->setCodigo((string) data_get($item, 'codigo', ''));
        }

        return $despatch->setDetails($detalles);
    }

    public function getNote($data)
    {
        if (!isset($data['serie']) || !isset($data['correlativo'])) {
            throw new \Exception('Serie y correlativo son obligatorios');
        }

        $igvCatalogService = new SunatIgvCatalogService();
        $details = $this->getDetails($data['items']);
        $totales = $igvCatalogService->calculateTotals($data['items']);

        return (new Note())
            ->setUblVersion('2.1')
            ->setTipoDoc($data['tipo_documento'])
            ->setSerie($data['serie'])
            ->setCorrelativo($data['correlativo'])
            ->setFechaEmision(new DateTime($data['fecha_emision']))
            ->setTipDocAfectado($data['tipDocAfectado'])
            ->setNumDocfectado($data['numDocAfectado'])
            ->setCodMotivo($data['codMotivo'])
            ->setDesMotivo($data['desMotivo'])
            ->setTipoMoneda($data['moneda'])
            ->setCompany($this->getCompany())
            ->setClient($this->getClient($data['cliente']))
            ->setMtoOperGravadas($totales['gravadas'])
            ->setMtoOperExoneradas($totales['exoneradas'])
            ->setMtoOperInafectas($totales['inafectas'])
            ->setMtoOperExportacion($totales['exportacion'])
            ->setMtoOperGratuitas($totales['gratuitas'])
            ->setMtoIGVGratuitas($totales['igv_gratuitas'])
            ->setMtoIGV($totales['igv'])
            ->setTotalImpuestos($totales['total_impuestos'])
            ->setValorVenta($totales['valor_venta'])
            ->setSubTotal($totales['sub_total'])
            ->setMtoImpVenta($totales['total'])
            ->setDetails($details)
            ->setLegends($this->getLegend($totales, $data['moneda']));
    }

    public function getCompany()
    {
        return (new Company())
            ->setRuc(config('empresa.ruc'))
            ->setRazonSocial(config('empresa.razon_social'))
            ->setNombreComercial(config('empresa.nombre_comercial'))
            ->setAddress($this->getAddress());
    }

    public function getClient($cliente)
    {
        return (new Client())
            ->setTipoDoc($cliente['tipo_doc'] ?? null)
            ->setNumDoc($cliente['num_doc'] ?? null)
            ->setRznSocial($cliente['razon_social'] ?? null);
    }

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

    public function getDetails($items)
    {
        $details = [];
        $igvCatalogService = new SunatIgvCatalogService();

        foreach ($items as $item) {
            $line = $igvCatalogService->calculateLine($item);

            $detail = (new SaleDetail())
                ->setCodProducto($item['codigo'])
                ->setUnidad($item['unidad'] ?? 'NIU')
                ->setCantidad($item['cantidad'])
                ->setMtoValorUnitario($line['mto_valor_unitario_sunat'])
                ->setDescripcion($item['descripcion'])
                ->setMtoBaseIgv($line['es_gratuita'] ? 0 : $line['base'])
                ->setPorcentajeIgv($line['porcentaje_igv'])
                ->setIgv($line['igv'])
                ->setTipAfeIgv($line['tip_afe_igv'])
                ->setTotalImpuestos($line['igv'])
                ->setMtoValorVenta($line['subtotal'])
                ->setMtoPrecioUnitario($line['mto_precio_unitario_sunat']);

            if ($line['es_gratuita']) {
                $detail->setMtoValorGratuito($line['mto_valor_gratuito']);
            }

            $details[] = $detail;
        }

        return $details;
    }

    public function calcularTotales($items)
    {
        $igvCatalogService = new SunatIgvCatalogService();
        return $igvCatalogService->calculateTotals($items);
    }

    public function getLegend(array $totales, string $moneda)
    {
        $formatter = new NumeroALetras();
        $monedaTexto = $moneda === 'USD' ? 'DOLARES AMERICANOS' : 'SOLES';

        $legends = [
            (new Legend())
                ->setCode('1000')
                ->setValue('SON ' . $formatter->toInvoice((float) $totales['total'], 2, $monedaTexto)),
        ];

        if (($totales['gratuitas'] ?? 0) > 0) {
            $legends[] = (new Legend())
                ->setCode('1002')
                ->setValue('TRANSFERENCIA GRATUITA DE BIENES Y/O SERVICIOS');
        }

        return $legends;
    }

    public function sunatResponse($result)
    {
        if (!$result->isSuccess()) {
            return [
                'success' => false,
                'error' => [
                    'code' => $result->getError()->getCode(),
                    'message' => $result->getError()->getMessage(),
                ],
            ];
        }

        $cdr = $result->getCdrResponse();

        return [
            'success' => true,
            'cdrRespuesta' => [
                'code' => (int) $cdr->getCode(),
                'description' => $cdr->getDescription(),
                'notes' => $cdr->getNotes(),
            ],
        ];
    }

    protected function buildFormaPago(array $data, array $totales)
    {
        $formaPago = strtolower((string) data_get($data, 'forma_pago', 'contado'));
        if ($formaPago !== 'credito') {
            return new FormaPagoContado();
        }

        $montoPendiente = (float) data_get($data, 'credito.monto_pendiente', $totales['total'] ?? 0);

        return new FormaPagoCredito($montoPendiente, (string) data_get($data, 'moneda', 'PEN'));
    }

    protected function buildCuotas(array $data, array $totales): array
    {
        $formaPago = strtolower((string) data_get($data, 'forma_pago', 'contado'));
        if ($formaPago !== 'credito') {
            return [];
        }

        $totalPendiente = (float) data_get($data, 'credito.monto_pendiente', $totales['total'] ?? 0);
        $totalCuotas = max((int) data_get($data, 'credito.cuotas', 1), 1);
        $moneda = (string) data_get($data, 'moneda', 'PEN');
        $fechaBase = data_get($data, 'credito.fecha_vencimiento');
        $fecha = new DateTime($fechaBase ?: 'now');

        $montoCuotaBase = round($totalPendiente / $totalCuotas, 2);
        $acumulado = 0.0;
        $cuotas = [];

        for ($i = 1; $i <= $totalCuotas; $i++) {
            $monto = $i === $totalCuotas
                ? round($totalPendiente - $acumulado, 2)
                : $montoCuotaBase;

            $acumulado += $monto;

            $cuotas[] = (new Cuota())
                ->setMoneda($moneda)
                ->setMonto($monto)
                ->setFechaPago((clone $fecha)->modify('+' . ($i - 1) . ' month'));
        }

        return $cuotas;
    }

    protected function buildDetraccion(array $data): ?Detraction
    {
        $aplica = in_array(data_get($data, 'detraccion.aplica', false), [true, 1, '1', 'true', 'on', 'yes'], true);
        if (!$aplica) {
            return null;
        }

        $codigo = (string) data_get($data, 'detraccion.codigo', '');
        $porcentaje = (float) data_get($data, 'detraccion.porcentaje', 0);
        $monto = (float) data_get($data, 'detraccion.monto', 0);
        $cuenta = preg_replace('/\D+/', '', (string) data_get($data, 'detraccion.cuenta', '')) ?? '';
        $medioPago = (string) data_get($data, 'detraccion.medio_pago', config('sunat_detraccion.medio_pago_default', '001'));

        if ($codigo === '' || $porcentaje <= 0 || $monto <= 0 || $cuenta === '') {
            return null;
        }

        return (new Detraction())
            ->setCodBienDetraccion($codigo)
            ->setPercent($porcentaje)
            ->setMount($monto)
            ->setCtaBanco($cuenta)
            ->setCodMedioPago($medioPago);
    }

    protected function splitDriverName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];

        if (count($parts) <= 1) {
            return [$fullName !== '' ? $fullName : '-', '-'];
        }

        $nombres = implode(' ', array_slice($parts, 0, -2));
        $apellidos = implode(' ', array_slice($parts, -2));

        if ($nombres === '') {
            $nombres = $parts[0] ?? '-';
        }

        return [$nombres, $apellidos !== '' ? $apellidos : '-'];
    }

    protected function resolveEnv(): string
    {
        return strtolower((string) config('empresa.sunat_env', 'beta')) === 'produccion'
            ? 'produccion'
            : 'beta';
    }

    protected function buildSee(string $endpoint): See
    {
        $certPath = (string) config('empresa.sunat_cert_path');
        $certificate = file_get_contents($certPath);

        $see = new See();
        $see->setCertificate($certificate);
        $see->setService($endpoint);
        $see->setClaveSOL(
            (string) config('empresa.sunat_ruc'),
            (string) config('empresa.sunat_username'),
            (string) config('empresa.sunat_password')
        );

        return $see;
    }
}
