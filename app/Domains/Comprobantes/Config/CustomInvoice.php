<?php

namespace App\Domains\Comprobantes\Config;

use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Exception;

class GreenterService
{
    public static function getSee()
    {
        $see = new See();

        // Leer los datos desde config/empresa.php
        $certPath = config('empresa.sunat_cert_path');
        $certPass = config('empresa.sunat_cert_pass');

        // Verificar si el archivo del certificado existe
        if (!file_exists($certPath)) {
            throw new Exception("El certificado no se encontró en la ruta: $certPath");
        }

        // Leer el contenido del archivo .pfx
        $pfx = file_get_contents($certPath);
        $certs = [];

        // Extraer el certificado y la clave privada
        if (!openssl_pkcs12_read($pfx, $certs, $certPass)) {
            throw new Exception("No se pudo leer el archivo PFX. Verifique la contraseña.");
        }

        // Configurar el certificado y la clave privada en Greenter
        $see->setCertificate($certs['cert'] . $certs['pkey']);

        // Credenciales SOL desde el archivo de configuración
        $ruc = config('empresa.sunat_ruc');
        $username = config('empresa.sunat_username');
        $password = config('empresa.sunat_password');

        if (empty($ruc) || empty($username) || empty($password)) {
            throw new Exception("Faltan credenciales SOL (RUC, usuario o contraseña).");
        }

        // Establecer las credenciales SOL
        $see->setClaveSOL($ruc, $username, $password);

        // Definir si es entorno de pruebas o producción
        $env = config('empresa.sunat_env') === 'beta' ? SunatEndpoints::FE_BETA : SunatEndpoints::FE_PRODUCCION;
        $see->setService($env);

        return $see;
    }
}