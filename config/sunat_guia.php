<?php

return [
    'motivos_traslado' => [
        ['codigo' => '01', 'descripcion' => 'Venta'],
        ['codigo' => '02', 'descripcion' => 'Compra'],
        ['codigo' => '04', 'descripcion' => 'Traslado entre establecimientos de la misma empresa'],
        ['codigo' => '08', 'descripcion' => 'Importacion'],
        ['codigo' => '09', 'descripcion' => 'Exportacion'],
        ['codigo' => '13', 'descripcion' => 'Otros'],
    ],
    'modalidades_transporte' => [
        ['codigo' => '01', 'descripcion' => 'Transporte publico'],
        ['codigo' => '02', 'descripcion' => 'Transporte privado'],
    ],
    'tipos_documento_guia' => [
        ['codigo' => '09', 'descripcion' => 'Guia de remision remitente'],
        ['codigo' => '31', 'descripcion' => 'Guia de remision transportista'],
    ],
    'documentos_relacionados' => [
        ['codigo' => '01', 'descripcion' => 'Factura'],
        ['codigo' => '03', 'descripcion' => 'Boleta de Venta'],
        ['codigo' => '09', 'descripcion' => 'Guia de Remision Remitente'],
        ['codigo' => '12', 'descripcion' => 'Constancia de Deposito - Detraccion'],
        ['codigo' => '00', 'descripcion' => 'Ticket o cinta emitido por maquina registradora'],
        ['codigo' => '04', 'descripcion' => 'Liquidacion de Compra'],
    ],
];
