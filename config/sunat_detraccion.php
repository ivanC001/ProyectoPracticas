<?php

return [
    'medio_pago_default' => '001',
    'cuenta_bn_default' => env('DETRACCION_CUENTA_BN', '00074157688'),
    'monto_minimo_servicios' => 700.00,
    'servicios' => [
        '019' => [
            'descripcion' => 'Arrendamiento de bienes',
            'porcentaje' => 10.00,
        ],
        '020' => [
            'descripcion' => 'Mantenimiento y reparacion de bienes muebles',
            'porcentaje' => 12.00,
        ],
        '021' => [
            'descripcion' => 'Movimiento de carga',
            'porcentaje' => 10.00,
        ],
        '022' => [
            'descripcion' => 'Otros servicios empresariales',
            'porcentaje' => 12.00,
        ],
        '025' => [
            'descripcion' => 'Fabricacion de bienes por encargo',
            'porcentaje' => 10.00,
        ],
        '027' => [
            'descripcion' => 'Servicio de transporte de carga',
            'porcentaje' => 4.00,
        ],
        '037' => [
            'descripcion' => 'Demas servicios gravados con el IGV',
            'porcentaje' => 12.00,
        ],
    ],
];
