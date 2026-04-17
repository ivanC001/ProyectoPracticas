<?php

return [
    'definitions' => [
        'admin' => [
            'label' => 'Administrador',
            'description' => 'Control total del sistema.',
            'default_path' => '/usuarios',
            'paths' => ['*'],
        ],
        'comercial' => [
            'label' => 'Comercial',
            'description' => 'Clientes, cotizaciones, ventas y catalogo.',
            'default_path' => '/clientes',
            'paths' => [
                '/',
                '/clientes',
                '/cotizaciones',
                '/cotizaciones/registro',
                '/producto',
                '/servicios',
                '/venta',
                '/reporte-ventas',
                '/notascredito',
                '/guias-remision',
            ],
        ],
        'operaciones' => [
            'label' => 'Operaciones',
            'description' => 'Conductores, unidades, rutas, gastos y reportes.',
            'default_path' => '/reporte-ruta',
            'paths' => [
                '/',
                '/conductor',
                '/camion',
                '/rutas',
                '/ruta',
                '/viaticos',
                '/combustible',
                '/reporte-ruta',
                '/guias-remision',
            ],
        ],
    ],
];
