<?php

namespace App\Services;

class SunatIgvCatalogService
{
    public function catalog(): array
    {
        return config('sunat_igv.catalog', []);
    }

    public function allowedCodes(): array
    {
        return array_keys($this->catalog());
    }

    public function normalizeCode(?string $code): string
    {
        $normalized = str_pad((string) ($code ?: '10'), 2, '0', STR_PAD_LEFT);

        return array_key_exists($normalized, $this->catalog()) ? $normalized : '10';
    }

    public function metadata(?string $code): array
    {
        $normalized = $this->normalizeCode($code);
        $catalog = $this->catalog();
        $meta = $catalog[$normalized];

        return [
            'code' => $normalized,
            'label' => $meta['label'],
            'group' => $meta['group'],
            'igv_rate' => (float) $meta['igv_rate'],
            'pdf_legend' => $meta['pdf_legend'],
        ];
    }

    public function calculateLine(array $item): array
    {
        $cantidad = max((float) ($item['cantidad'] ?? 0), 0);
        $valorUnitarioInput = max((float) ($item['valor_unitario'] ?? 0), 0);
        $descuento = max((float) ($item['descuento'] ?? 0), 0);

        $base = max(round(($cantidad * $valorUnitarioInput) - $descuento, 2), 0);
        $meta = $this->metadata($item['tip_afe_igv'] ?? '10');

        $esGratuita = $meta['group'] === 'gratuita';
        $aplicaIgv = $meta['group'] === 'gravada';

        $igv = $aplicaIgv ? round($base * $meta['igv_rate'], 2) : 0.00;
        $valorVenta = $esGratuita ? 0.00 : $base;
        $total = $esGratuita ? 0.00 : round($valorVenta + $igv, 2);

        $mtoValorUnitarioSunat = $esGratuita
            ? 0.00
            : ($cantidad > 0 ? round($valorVenta / $cantidad, 10) : 0.00);

        $mtoPrecioUnitarioSunat = $esGratuita
            ? 0.00
            : ($cantidad > 0 ? round($total / $cantidad, 10) : 0.00);

        return [
            'tip_afe_igv' => $meta['code'],
            'tip_afe_label' => $meta['label'],
            'group' => $meta['group'],
            'aplica_igv' => $aplicaIgv,
            'es_gratuita' => $esGratuita,
            'porcentaje_igv' => $aplicaIgv ? ($meta['igv_rate'] * 100) : 0.00,
            'base' => $base,
            'descuento' => $descuento,
            'igv' => $igv,
            'subtotal' => $valorVenta,
            'total' => $total,
            'valor_unitario_input' => $valorUnitarioInput,
            'mto_valor_unitario_sunat' => $mtoValorUnitarioSunat,
            'mto_precio_unitario_sunat' => $mtoPrecioUnitarioSunat,
            'mto_valor_gratuito' => $esGratuita ? $base : 0.00,
            'pdf_legend' => $meta['pdf_legend'],
        ];
    }

    public function calculateTotals(iterable $items): array
    {
        $totales = [
            'gravadas' => 0.00,
            'exoneradas' => 0.00,
            'inafectas' => 0.00,
            'exportacion' => 0.00,
            'gratuitas' => 0.00,
            'igv' => 0.00,
            'igv_gratuitas' => 0.00,
            'valor_venta' => 0.00,
            'sub_total' => 0.00,
            'total' => 0.00,
            'total_impuestos' => 0.00,
        ];

        foreach ($items as $item) {
            $line = $this->calculateLine((array) $item);

            switch ($line['group']) {
                case 'gravada':
                    $totales['gravadas'] += $line['base'];
                    break;
                case 'exonerada':
                    $totales['exoneradas'] += $line['base'];
                    break;
                case 'inafecta':
                    $totales['inafectas'] += $line['base'];
                    break;
                case 'exportacion':
                    $totales['exportacion'] += $line['base'];
                    break;
                case 'gratuita':
                    $totales['gratuitas'] += $line['base'];
                    break;
            }

            $totales['igv'] += $line['igv'];
        }

        $totales['gravadas'] = round($totales['gravadas'], 2);
        $totales['exoneradas'] = round($totales['exoneradas'], 2);
        $totales['inafectas'] = round($totales['inafectas'], 2);
        $totales['exportacion'] = round($totales['exportacion'], 2);
        $totales['gratuitas'] = round($totales['gratuitas'], 2);
        $totales['igv'] = round($totales['igv'], 2);
        $totales['igv_gratuitas'] = round($totales['igv_gratuitas'], 2);

        $totales['valor_venta'] = round(
            $totales['gravadas'] + $totales['exoneradas'] + $totales['inafectas'] + $totales['exportacion'],
            2
        );
        $totales['sub_total'] = round($totales['valor_venta'] + $totales['igv'], 2);
        $totales['total'] = $totales['sub_total'];
        $totales['total_impuestos'] = round($totales['igv'] + $totales['igv_gratuitas'], 2);

        return $totales;
    }

    public function buildPdfLegends(array $totales): array
    {
        $legends = [];

        if (($totales['exoneradas'] ?? 0) > 0) {
            $legends[] = 'OPERACION EXONERADA DEL IGV';
        }

        if (($totales['inafectas'] ?? 0) > 0) {
            $legends[] = 'OPERACION INAFECTA DEL IGV';
        }

        if (($totales['exportacion'] ?? 0) > 0) {
            $legends[] = 'OPERACION DE EXPORTACION';
        }

        if (($totales['gratuitas'] ?? 0) > 0) {
            $legends[] = 'TRANSFERENCIA GRATUITA DE BIENES Y/O SERVICIOS';
        }

        return array_values(array_unique($legends));
    }
}
