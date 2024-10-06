<?php

namespace App\Domains\Reportes\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteExportService{
    public function exportarRutas($rutas)
    {
        // Crear un nuevo archivo Excel
        $spreadsheet = new Spreadsheet();

        // Hoja 1: Resumen de Rutas
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen de Rutas');

        // Encabezados de la hoja de resumen
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Origen');
        $sheet->setCellValue('C1', 'Destino');
        $sheet->setCellValue('D1', 'Fecha Inicio');
        $sheet->setCellValue('E1', 'Fecha Fin');
        $sheet->setCellValue('F1', 'Total Viáticos');
        $sheet->setCellValue('G1', 'Total Combustible');

        // Agregar los datos de las rutas en la hoja de resumen
        $row = 2;
        foreach ($rutas as $ruta) {
            $sheet->setCellValue('A' . $row, $ruta->id);
            $sheet->setCellValue('B' . $row, $ruta->origen);
            $sheet->setCellValue('C' . $row, $ruta->destino);
            $sheet->setCellValue('D' . $row, $ruta->fecha_inicio);
            $sheet->setCellValue('E' . $row, $ruta->fecha_fin);
            $sheet->setCellValue('F' . $row, $ruta->viaticos_sum_importe ?: 0);  // Total viáticos, si no tiene, poner 0
            $sheet->setCellValue('G' . $row, $ruta->combustibles_sum_importe ?: 0); // Total combustibles, si no tiene, poner 0
            $row++;
        }

        // Crear una hoja por cada ruta
        foreach ($rutas as $ruta) {
            // Crear una nueva hoja para la ruta
            $newSheet = $spreadsheet->createSheet();
            $newSheet->setTitle('Ruta_' . $ruta->id);

            // Encabezados para los detalles de viáticos y combustible
            $newSheet->setCellValue('A1', 'Servicio');
            $newSheet->setCellValue('B1', 'Fecha');
            $newSheet->setCellValue('C1', 'Número Factura');
            $newSheet->setCellValue('D1', 'Importe');

            // Obtener detalles de viáticos (supongamos que están relacionados con la ruta)
            $viaticos = $ruta->viaticos;  // Accede a los viáticos relacionados con la ruta

            // Añadir los detalles de viáticos si existen
            $row = 2;
            if ($viaticos && count($viaticos) > 0) {
                foreach ($viaticos as $viatico) {
                    $newSheet->setCellValue('A' . $row, 'Viático');
                    $newSheet->setCellValue('B' . $row, $viatico->fecha);
                    $newSheet->setCellValue('C' . $row, $viatico->num_factura);
                    $newSheet->setCellValue('D' . $row, $viatico->importe);
                    $row++;
                }
            } else {
                // Si no hay viáticos
                $newSheet->setCellValue('A' . $row, 'No hay registros de viáticos');
            }

            // Añadir los detalles de combustible (supongamos que están relacionados con la ruta)
            $combustibles = $ruta->combustibles;  // Accede a los combustibles relacionados con la ruta

            // Dejar dos filas vacías antes de agregar los combustibles
            $row += 2;

            // Añadir los detalles de combustible si existen
            if ($combustibles && count($combustibles) > 0) {
                foreach ($combustibles as $combustible) {
                    $newSheet->setCellValue('A' . $row, 'Combustible');
                    $newSheet->setCellValue('B' . $row, $combustible->fecha_hora);
                    $newSheet->setCellValue('C' . $row, $combustible->num_factura);
                    $newSheet->setCellValue('D' . $row, $combustible->importe);
                    $row++;
                }
            } else {
                // Si no hay combustibles
                $newSheet->setCellValue('A' . $row, 'No hay registros de combustible');
            }
        }

        // Descargar el archivo Excel
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        // Configuración de los encabezados para la descarga del archivo
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="reporte_rutas.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}