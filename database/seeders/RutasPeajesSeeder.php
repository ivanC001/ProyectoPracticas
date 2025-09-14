<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class RutasPeajesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('peajes')->insert([
            [
                'ruta_id' => 1, // debe existir la ruta
                'nombre' => 'Peaje Chillón',
                'importe' => 12.50,
                'fecha_hora' => Carbon::parse('2025-09-01 08:15:00'),
                'comprobante' => 'PEA-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ruta_id' => 1,
                'nombre' => 'Peaje Huacho',
                'importe' => 15.00,
                'fecha_hora' => Carbon::parse('2025-09-01 12:45:00'),
                'comprobante' => 'PEA-002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ruta_id' => 2,
                'nombre' => 'Peaje Virú',
                'importe' => 10.00,
                'fecha_hora' => Carbon::parse('2025-09-05 09:30:00'),
                'comprobante' => 'PEA-003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ruta_id' => 2,
                'nombre' => 'Peaje Chicama',
                'importe' => 8.50,
                'fecha_hora' => Carbon::parse('2025-09-05 10:15:00'),
                'comprobante' => 'PEA-004',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
