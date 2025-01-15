<?php

namespace App\Domains\Comprobantes\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class GenerarPDF {

    public function generarPDFConQR($venta, $detalles)
    {
        // Calcular subtotal si no está definido en los detalles
        foreach ($detalles as &$detalle) {
            if (!isset($detalle['subtotal'])) {
                $detalle['subtotal'] = $detalle['cantidad'] * $detalle['valor_unitario'];
            }
        }

        // Datos para el QR
        $qrData = "{$venta->ruc_empresa}|{$venta->tipo_documento}|{$venta->serie}-{$venta->correlativo}|{$venta->total_venta}|{$venta->fecha_emision}";

        // Crear el código QR usando Endroid QRCode
        $qrCode = QrCode::create($qrData)
            ->setSize(100)
            ->setMargin(10);

        // Crear el escritor para el QR y guardarlo en PNG en base64
        $writer = new PngWriter();
        $qrImage = $writer->write($qrCode);
        $qrBase64 = base64_encode($qrImage->getString()); // Convertir a base64

        // Construcción del HTML en línea sin utilizar una vista Blade
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Factura Electrónica</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .header, .footer { text-align: center; }
                .table { width: 100%; border-collapse: collapse; }
                .table th, .table td { border: 1px solid #000; padding: 5px; text-align: center; }
                .qr { text-align: center; margin-top: 20px; }
                .info { margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>{$venta->nombre_empresa}</h1>
                <p>RUC: {$venta->ruc_empresa}</p>
                <p>Fecha de Emisión: {$venta->fecha_emision}</p>
            </div>

            <h2>Factura {$venta->serie}-{$venta->correlativo}</h2>
            <p><strong>Cliente:</strong> {$venta->nombre_cliente}</p>
            <p><strong>Total Venta:</strong> S/. " . number_format($venta->total_venta, 2) . "</p>

            <h2>Detalles de la Factura</h2>
            <table class='table'>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>";
                
        foreach ($detalles as $detalle) {
            $html .= "
                    <tr>
                        <td>{$detalle['descripcion']}</td>
                        <td>{$detalle['cantidad']}</td>
                        <td>S/. " . number_format($detalle['valor_unitario'], 2) . "</td>
                        <td>S/. " . number_format($detalle['subtotal'], 2) . "</td>
                    </tr>";
        }

        $html .= "
                </tbody>
            </table>

            <h3>Total Venta: S/. " . number_format($venta->total_venta, 2) . "</h3>

            <div class='qr'>
                <p>Código QR para verificación:</p>
                <img src='data:image/png;base64,{$qrBase64}' alt='Código QR' style='width: 100px;'>
            </div>
        </body>
        </html>";

        // Configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Cargar el HTML en Dompdf y configurar el papel
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Definir la ruta donde se guardará el PDF
        $pdfFileName = "comprobantes/pdf/{$venta->serie}-{$venta->correlativo}.pdf";
        Storage::put($pdfFileName, $dompdf->output());

        // Retornar la ruta del archivo PDF
        return $pdfFileName;
    }
}
