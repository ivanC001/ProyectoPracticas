<?php

return [
    'ruc' => env('EMPRESA_RUC', '20123456789'),
    'razon_social' => env('HECAB SERVICIOS ENERGETICOS', 'hECAB S.A.C.'),
    'nombre_comercial' => env('EMPRESA_NOMBRE_COMERCIAL', 'Mi Empresa'),

    //direccion empresa emite comprobante
    'direccion' => env('EMPRESA_DIRECCION', 'Av. Ejemplo 123'),
    'urbanizacion' => env('EMPRESA_URBANIZACION', '-'),
    'ubigeo' => env('EMPRESA_UBIGEO', '150101'),
    'departamento' => env('EMPRESA_DEPARTAMENTO', 'LIMA'),
    'provincia' => env('EMPRESA_PROVINCIA', 'LIMA'),
    'distrito' => env('EMPRESA_DISTRITO', 'LIMA'),
    'cod_local' => env('EMPRESA_COD_LOCAL', '0000'),
    


    // Certificados y credenciales SUNAT
    'sunat_ruc' => env('SUNAT_RUC', '20123456789'),
    'sunat_username' => env('SUNAT_USERNAME', 'MODDATOS'),
    'sunat_password' => env('SUNAT_PASSWORD', 'MODDATOS'),
    'sunat_cert_path' => env('SUNAT_CERT_PATH', storage_path('certificates/ejemplo123456789.pem')),
    'sunat_cert_pass' => env('SUNAT_CERT_PASS', '123456789'),
    'sunat_env' => env('SUNAT_ENV', 'beta') // Usar beta o producción
]; 