<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RutasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rutas')->insert([
            [
                'fecha_inicio' => Carbon::parse('2025-09-01'),
                'fecha_fin' => Carbon::parse('2025-09-05'),
                'origen' => 'Lima',
                'destino' => 'Arequipa',
                'conductor_id' => 1, // asegúrate que exista un conductor con este ID
                'camion_id' => 1,    // asegúrate que exista un camión con este ID
                'caja_chica' => 500.00,
                'estado' => 'pendiente',
                'pago_viaje' => 2000.00,
                'ganancia_viaje' => 1500.00,
                'observaciones' => 'Viaje programado sin contratiempos.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fecha_inicio' => Carbon::parse('2025-09-10'),
                'fecha_fin' => Carbon::parse('2025-09-15'),
                'origen' => 'Trujillo',
                'destino' => 'Piura',
                'conductor_id' => 2,
                'camion_id' => 2,
                'caja_chica' => 300.00,
                'estado' => 'en curso',
                'pago_viaje' => 1500.00,
                'ganancia_viaje' => 1200.00,
                'observaciones' => 'Carga especial refrigerada.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
