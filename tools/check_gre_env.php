<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'client_id=' . (string) config('empresa.sunat_gre_client_id') . PHP_EOL;
echo 'client_secret=' . (string) config('empresa.sunat_gre_client_secret') . PHP_EOL;
echo 'env_id=' . (string) env('SUNAT_GRE_CLIENT_ID') . PHP_EOL;
echo 'env_secret=' . (string) env('SUNAT_GRE_CLIENT_SECRET') . PHP_EOL;

