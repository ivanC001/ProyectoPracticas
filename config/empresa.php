<?php

return [
    'ruc' => '20123456789',
    'razon_social' => 'HECAB Servicios Energeticos S.A.C.',
    'nombre_comercial' => 'HECAB SERVICIOS ENERGETICOS',

    //direccion empresa emite comprobante
    'direccion' => 'MZA. H LOTE. 3 A.H. FELIX MORENO CABALLERO (ALT. PARADERO GRIFO LA PLAYA), DISTRITO DE VENTANILLA, PROVINCIA CONSTITUCIONAL DEL CALLAO',
    'urbanizacion' => '-',
    'ubigeo' => '150101',
    'departamento' => 'PROVINCIA CONSTITUCIONAL DEL CALLAO',
    'provincia' => 'CALLAO',
    'distrito' => 'VENTANILLA',
    'cod_local' => '0000',
    'telefono' => '988-724-928',
    'emails' => [
        'energeticos.hecab@gmail.com',
        'josercb.86@gmail.com',
    ],
    'alerta_emails' => [
        'energeticos.hecab@gmail.com',
        'josercb.86@gmail.com',
    ],
    'gerente_cargo' => 'GERENTE DE HECAB S.A.C.',
    'gerente_nombre' => 'Jose Ramiro Calderon Burga',
    'medios_pago' => [
        'detraccion_bn' => [
            'label' => 'Cta. Cte. Detraccion Banco de la Nacion',
            'detalle' => '00-074-157688',
            'color' => '#1f2937',
        ],
        'cta_bbva' => [
            'label' => 'Cta. Cte. BBVA',
            'detalle' => '0011 0339 0100024350 99',
            'color' => '#2563eb',
        ],
        'cci_bbva' => [
            'label' => 'CCI (Interbancario) BBVA',
            'detalle' => '011 339 000100024350 99',
            'color' => '#2563eb',
        ],
        'cta_bcp' => [
            'label' => 'Cta. Cte. BCP',
            'detalle' => '192-2624745-074',
            'color' => '#1f2937',
        ],
        'cci_bcp' => [
            'label' => 'CCI (Interbancario) BCP',
            'detalle' => '00219200262474507430',
            'color' => '#1f2937',
        ],
        'cta_bcp_usd' => [
            'label' => 'Cta. Cte. BCP USD',
            'detalle' => '192-2665517-1-22',
            'color' => '#dc2626',
        ],
        'cci_bcp_usd' => [
            'label' => 'CCI (Interbancario) BCP USD',
            'detalle' => '00219200266551712236',
            'color' => '#dc2626',
        ],
    ],
    


    // Certificados y credenciales SUNAT
    'sunat_ruc' => env('SUNAT_RUC', ''),
    'sunat_username' => env('SUNAT_USERNAME', ''),
    'sunat_password' => env('SUNAT_PASSWORD', ''),
    'sunat_gre_client_id' => env('SUNAT_GRE_CLIENT_ID', ''),
    'sunat_gre_client_secret' => env('SUNAT_GRE_CLIENT_SECRET', ''),
    'sunat_gre_base_url' => env('SUNAT_GRE_BASE_URL', ''),
    'sunat_gre_auth_url' => env('SUNAT_GRE_AUTH_URL', 'https://api-seguridad.sunat.gob.pe/v1'),
    'sunat_gre_cpe_url' => env('SUNAT_GRE_CPE_URL', 'https://api-cpe.sunat.gob.pe/v1'),
    'sunat_cert_path' => env('SUNAT_CERT_PATH', storage_path('certificates/ejemplo123456789.pem')),
    'sunat_cert_pass' => env('SUNAT_CERT_PASS', '123456789'),
    'sunat_env' => env('SUNAT_ENV', 'beta') // Usar beta o producción
];
