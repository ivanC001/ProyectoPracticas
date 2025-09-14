<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Database\Seeders\RutasViaticosSeeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario de prueba
        $this->call([
            ConductoresSeeder::class,        // primero los conductores
            CaminonesSeeder::class,          // luego los camiones
            RutasSeeder::class,              // después las rutas (depende de conductores y camiones)
            RutasViaticosSeeder::class,      // después viáticos (depende de rutas)
            RutasPeajesSeeder::class,        // después peajes (depende de rutas)
            RutasCombustiblesSeeder::class,  // después combustibles (depende de rutas)
        ]);



        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        
    }
}
