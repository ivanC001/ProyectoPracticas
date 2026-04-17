<?php

$empresaEmails = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('EMPRESA_EMAILS', 'energeticos.hecab@gmail.com, josercb.86@gmail.com'))
)));

$alertaEmails = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('SEGUROS_ALERTA_EMAILS', implode(',', $empresaEmails)))
)));

return [
    'ruc' => env('EMPRESA_RUC', '20123456789'),
    'razon_social' => env('HECAB SERVICIOS ENERGETICOS', 'HECAB Servicios Energeticos S.A.C.'),
    'nombre_comercial' => env('EMPRESA_NOMBRE_COMERCIAL', 'HECAB SERVICIOS ENERGETICOS'),

    //direccion empresa emite comprobante
    'direccion' => env('EMPRESA_DIRECCION', 'MZA. H LOTE. 3 A.H. FELIX MORENO CABALLERO (ALT. PARADERO GRIFO LA PLAYA), DISTRITO DE 
        VENTANILLA, PROVINCIA CONSTITUCIONAL DEL CALLAO'),
    'urbanizacion' => env('EMPRESA_URBANIZACION', '-'),
    'ubigeo' => env('EMPRESA_UBIGEO', '150101'),
    'departamento' => env('EMPRESA_DEPARTAMENTO', 'PROVINCIA CONSTITUCIONAL DEL CALLAO'),
    'provincia' => env('EMPRESA_PROVINCIA', 'CALLAO'),
    'distrito' => env('EMPRESA_DISTRITO', 'VENTANILLA'),
    'cod_local' => env('EMPRESA_COD_LOCAL', '0000'),
    'telefono' => env('EMPRESA_TELEFONO', '988-724-928'),
    'emails' => $empresaEmails,
    'alerta_emails' => $alertaEmails,
    'gerente_cargo' => env('EMPRESA_GERENTE_CARGO', 'GERENTE DE HECAB S.A.C.'),
    'gerente_nombre' => env('EMPRESA_GERENTE_NOMBRE', 'Jose Ramiro Calderon Burga'),
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
    'sunat_ruc' => env('SUNAT_RUC', '20123456789'),
    'sunat_username' => env('SUNAT_USERNAME', 'MODDATOS'),
    'sunat_password' => env('SUNAT_PASSWORD', 'MODDATOS'),
    'sunat_gre_client_id' => env('SUNAT_GRE_CLIENT_ID', ''),
    'sunat_gre_client_secret' => env('SUNAT_GRE_CLIENT_SECRET', ''),
    'sunat_gre_auth_url' => env('SUNAT_GRE_AUTH_URL', 'https://api-seguridad.sunat.gob.pe/v1'),
    'sunat_gre_cpe_url' => env('SUNAT_GRE_CPE_URL', 'https://api-cpe.sunat.gob.pe/v1'),
    'sunat_cert_path' => env('SUNAT_CERT_PATH', storage_path('certificates/ejemplo123456789.pem')),
    'sunat_cert_pass' => env('SUNAT_CERT_PASS', '123456789'),
    'sunat_env' => env('SUNAT_ENV', 'beta') // Usar beta o producción
]; 
