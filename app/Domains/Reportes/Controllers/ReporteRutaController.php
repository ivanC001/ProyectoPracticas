<?php

namespace App\Domains\Reportes\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReporteRutaController extends Controller
{
    public function exportRutaDetalle($id)
    {
        $ruta = Ruta::with(['viaticos', 'combustibles', 'peajes', 'camion', 'conductor'])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ===== Estilos =====
        $tituloPrincipal = [
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '2E7D32']]
        ];

        $tituloSeccion = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1976D2']]
        ];

        $cabecera = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '0288D1']]
        ];

        $celdas = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '999999']
                ]
            ]
        ];

        // ===== Título =====
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', "REPORTE DE RUTA #{$ruta->id}");
        $sheet->getStyle('A1')->applyFromArray($tituloPrincipal);
        $sheet->getRowDimension('1')->setRowHeight(30);

        // ===== Info General en 2 filas verticales =====
        $row = 3;

        $camposFila1 = [
            'Fecha Inicio' => $ruta->fecha_inicio,
            'Fecha Fin'    => $ruta->fecha_fin,
            'Origen'       => $ruta->origen,
            'Destino'      => $ruta->destino,
            'Conductor'    => $ruta->conductor ? ($ruta->conductor->nombre . ' ' . $ruta->conductor->apellido) : 'N/A'
        ];

        $col = 'A';
        foreach ($camposFila1 as $campo => $valor) {
            $sheet->setCellValue("{$col}{$row}", $campo);
            $sheet->getStyle("{$col}{$row}")->applyFromArray($cabecera);
            $sheet->setCellValue("{$col}" . ($row + 1), $valor);
            $sheet->getStyle("{$col}" . ($row + 1))->applyFromArray($celdas);
            $col++;
        }

        $row += 3;
        $camposFila2 = [
            'Placa Tracto'  => $ruta->camion->placa_tracto ?? 'N/A',
            'Placa Carreto' => $ruta->camion->placa_carreto ?? 'N/A',
            'Caja Chica'    => number_format(floatval($ruta->caja_chica), 2),
            'Pago del viaje'=> number_format(floatval($ruta->pago_viaje), 2),
            'Observaciones' => $ruta->observaciones ?? ''
        ];

        $col = 'A';
        foreach ($camposFila2 as $campo => $valor) {
            $sheet->setCellValue("{$col}{$row}", $campo);
            $sheet->getStyle("{$col}{$row}")->applyFromArray($cabecera);
            $sheet->setCellValue("{$col}" . ($row + 1), $valor);
            $sheet->getStyle("{$col}" . ($row + 1))->applyFromArray($celdas);
            $col++;
        }

        // ===== Viáticos =====
        $row += 3;
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'VIÁTICOS');
        $sheet->getStyle("A{$row}")->applyFromArray($tituloSeccion);

        $row++;
        $sheet->fromArray(['Fecha','Comprobante','Servicio','Importe'], null, "A{$row}");
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($cabecera);

        $totalViaticos = 0;
        foreach ($ruta->viaticos as $v) {
            $row++;
            $sheet->setCellValue("A{$row}", $v->fecha ?? '');
            $sheet->setCellValue("B{$row}", $v->numero_factura ?? '');
            $sheet->setCellValue("C{$row}", $v->nombre_servicio ?? $v->descripcion ?? '');
            $sheet->setCellValue("D{$row}", floatval($v->importe ?? 0));
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($celdas);
            $totalViaticos += floatval($v->importe ?? 0);
        }

        // ===== Combustibles =====
        $row += 2;
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("A{$row}", 'COMBUSTIBLES');
        $sheet->getStyle("A{$row}")->applyFromArray($tituloSeccion);

        $row++;
        $sheet->fromArray(['Fecha','Comprobante','Grifo','Galones','Importe'], null, "A{$row}");
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($cabecera);

        $totalComb = 0;
        foreach ($ruta->combustibles as $c) {
            $row++;
            $sheet->setCellValue("A{$row}", $c->fecha_hora ?? '');
            $sheet->setCellValue("B{$row}", $c->num_factura ?? '');
            $sheet->setCellValue("C{$row}", $c->grifo ?? '');
            $sheet->setCellValue("D{$row}", floatval($c->galonesCombustible ?? 0));
            $sheet->setCellValue("E{$row}", floatval($c->importe ?? 0));
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($celdas);
            $totalComb += floatval($c->importe ?? 0);
        }

        // ===== Peajes =====
        $row += 2;
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'PEAJES');
        $sheet->getStyle("A{$row}")->applyFromArray($tituloSeccion);

        $row++;
        $sheet->fromArray(['Fecha','Nombre','Comprobante','Importe'], null, "A{$row}");
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($cabecera);

        $totalPeajes = 0;
        foreach ($ruta->peajes as $p) {
            $row++;
            $sheet->setCellValue("A{$row}", $p->fecha_hora ?? '');
            $sheet->setCellValue("B{$row}", $p->nombre ?? '');
            $sheet->setCellValue("C{$row}", $p->comprobante ?? '');
            $sheet->setCellValue("D{$row}", floatval($p->importe ?? 0));
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($celdas);
            $totalPeajes += floatval($p->importe ?? 0);
        }

        // ===== Totales =====
        $row += 2;
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'RESUMEN DE GASTOS');
        $sheet->getStyle("A{$row}")->applyFromArray($tituloSeccion);

        $row++;
        $gastosTotales = $totalViaticos + $totalComb + $totalPeajes;
        $ingresos = floatval($ruta->pago_viaje) + floatval($ruta->caja_chica);
        $ganancia = $ingresos - $gastosTotales;

        // Cálculo IGV y detracción
        $igv = $ruta->pago_viaje * 0.18;
        $pagoConIgv = $ruta->pago_viaje + $igv;
        $detraccion = $pagoConIgv * 0.04;

        $resumen = [
            'Total Viáticos'    => $totalViaticos,
            'Total Combustible' => $totalComb,
            'Total Peajes'      => $totalPeajes,
            'GASTOS TOTALES'    => $gastosTotales,
            'Pago Viaje (sin IGV)' => $ruta->pago_viaje,
            'IGV (18%)'            => $igv,
            'Pago con IGV'         => $pagoConIgv,
            'Detracción (4%)'      => $detraccion,
            'Caja Chica'           => $ruta->caja_chica,
            'GANANCIA VIAJE'       => $ganancia,
        ];

        foreach ($resumen as $campo => $valor) {
            $sheet->setCellValue("A{$row}", $campo);
            $sheet->setCellValue("B{$row}", number_format($valor, 2));
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($celdas);
            $row++;
        }

        // Resaltar ganancia
        $sheet->getStyle("A" . ($row - 1) . ":B" . ($row - 1))
              ->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('D32F2F');

        // Ajuste columnas
        foreach (range('A','J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Guardar ganancia
        $ruta->ganancia_viaje = $ganancia;
        $ruta->save();

        // Descargar archivo
        $writer = new Xlsx($spreadsheet);
        $fileName = "reporte_ruta_{$ruta->id}.xlsx";
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }
}
