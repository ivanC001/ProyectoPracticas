<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CaminonesSeeder::class,
            ConductoresSeeder::class,
            RutasSeeder::class,
            RutasViaticosSeeder::class,
            RutasPeajesSeeder::class,
            RutasCombustiblesSeeder::class,
            TipoDocumentoSeeder::class,
        ]);
    }
}
