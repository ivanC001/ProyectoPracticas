<?php

namespace App\Domains\Comprobantes\Config;

use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Exception;

class CertificadoConfig
{
    public static function getSee()
    {
        $see = new See();
        $certPath = storage_path(config('empresa.sunat_cert_path'));
        $certPass = config('empresa.sunat_cert_pass');

        if (!file_exists($certPath)) {
            throw new Exception("El certificado no se encontró en la ruta: $certPath");
        }

        $pfx = file_get_contents($certPath);
        $certs = [];

        if (!openssl_pkcs12_read($pfx, $certs, $certPass)) {
            throw new Exception("No se pudo leer el archivo PFX. Verifique la contraseña.");
        }

        $see->setCertificate($certs['cert'] . $certs['pkey']);

        $ruc = config('empresa.sunat_ruc');
        $username = config('empresa.sunat_username');
        $password = config('empresa.sunat_password');

        if (empty($ruc) || empty($username) || empty($password)) {
            throw new Exception("Faltan credenciales SOL (RUC, usuario o contraseña).");
        }

        $see->setClaveSOL($ruc, $username, $password);

        $env = config('empresa.sunat_env') === 'beta' ? SunatEndpoints::FE_BETA : SunatEndpoints::FE_PRODUCCION;
        $see->setService($env);

        return $see;
    }
}
