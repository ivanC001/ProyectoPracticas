<?php

return [
    'ruc' => env('EMPRESA_RUC', '20123456789'),
    'razon_social' => env('EMPRESA_RAZON_SOCIAL', 'Mi Empresa S.A.C.'),
    'nombre_comercial' => env('EMPRESA_NOMBRE_COMERCIAL', 'Mi Empresa'),
    'direccion' => env('EMPRESA_DIRECCION', 'Av. Ejemplo 123'), 

    // Certificados y credenciales SUNAT
    'sunat_ruc' => env('SUNAT_RUC', '20123456789'),
    'sunat_username' => env('SUNAT_USERNAME', 'MODDATOS'),
    'sunat_password' => env('SUNAT_PASSWORD', 'MODDATOS'),
    'sunat_cert_path' => env('SUNAT_CERT_PATH', storage_path('certificates/demo.pfx')),
    'sunat_cert_pass' => env('SUNAT_CERT_PASS', null),
    'sunat_env' => env('SUNAT_ENV', 'beta') // Usar beta o producción
];