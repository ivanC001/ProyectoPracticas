<?php

namespace App\Domains\Comprobantes\Services;

use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Exception;

class GreenterService
{
    public static function getSee()
    {
        $see = new See();

        // Ruta completa al archivo .pfx y la contraseña desde el archivo .env
        $certPath = storage_path(env('SUNAT_CERT_PATH')); // Ruta del certificado
        $certPass = env('SUNAT_CERT_PASS'); // Contraseña del certificado

        // Verificar si el archivo del certificado existe
        if (!file_exists($certPath)) {
            throw new Exception("El certificado no se encontró en la ruta: $certPath");
        }

        // Leer el contenido del archivo .pfx
        $pfx = file_get_contents($certPath);

        // Extraer el certificado y la clave privada usando la contraseña
        $certs = [];
        if (!openssl_pkcs12_read($pfx, $certs, $certPass)) {
            throw new Exception("No se pudo leer el archivo PFX. Verifique la contraseña.");
        }

        // Verificar que se haya extraído tanto el certificado como la clave privada
        if (empty($certs['pkey']) || empty($certs['cert'])) {
            throw new Exception("No se pudo extraer la clave privada o el certificado del archivo PFX.");
        }

        // Configurar el certificado y la clave privada en Greenter
        // Greenter maneja ambos desde setCertificate
        $see->setCertificate($certs['cert'] . $certs['pkey']);  // Pasa el certificado junto con la clave privada

        // Configurar las credenciales SOL (RUC + usuario + contraseña)
        $ruc = env('SUNAT_RUC');
        $username = env('SUNAT_USERNAME');
        $password = env('SUNAT_PASSWORD');

        if (empty($ruc) || empty($username) || empty($password)) {
            throw new Exception("Faltan credenciales SOL (RUC, usuario o contraseña).");
        }

        // Establecer las credenciales SOL en el See
        $see->setClaveSOL($ruc, $username, $password);

        // Definir el entorno de pruebas o producción
        $see->setService(SunatEndpoints::FE_BETA); // Cambiar a FE_PRODUCCION para producción

        return $see;
    }
}