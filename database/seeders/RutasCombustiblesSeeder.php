<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class RutasCombustiblesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('combustibles')->insert([
            [
                'ruta_id' => 1, // Asegúrate que exista esta ruta
                'num_factura' => 'CMB-001',
                'grifo' => 'Grifo Repsol - Lima',
                'fecha_hora' => Carbon::parse('2025-09-02 10:30:00'),
                'galonesCombustible' => 50.75,
                'importe' => 750.00,
                'kilometraje_inicial' => 12000,
                'kilometraje_final' => 12500,
                'tipo_combustible' => 'Diesel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ruta_id' => 1,
                'num_factura' => 'CMB-002',
                'grifo' => 'Grifo Primax - Trujillo',
                'fecha_hora' => Carbon::parse('2025-09-03 14:15:00'),
                'galonesCombustible' => 30.50,
                'importe' => 450.00,
                'kilometraje_inicial' => 12500,
                'kilometraje_final' => 12850,
                'tipo_combustible' => 'Diesel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ruta_id' => 2,
                'num_factura' => 'CMB-003',
                'grifo' => 'Grifo Petroperú - Piura',
                'fecha_hora' => Carbon::parse('2025-09-11 09:00:00'),
                'galonesCombustible' => 60.00,
                'importe' => 900.00,
                'kilometraje_inicial' => 21000,
                'kilometraje_final' => 21600,
                'tipo_combustible' => 'Gasolina 95',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
