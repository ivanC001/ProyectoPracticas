<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RutasViaticosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('viaticos')->insert([
            [
                'ruta_id' => 1, // Asegúrate que exista la ruta con este ID
                'nombre_servicio' => 'Hospedaje',
                'fecha' => Carbon::parse('2025-09-02'),
                'numero_factura' => 'FAC-001',
                'importe' => 250.00,
                'descripcion' => 'Hotel en Lima por 1 noche',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ruta_id' => 1,
                'nombre_servicio' => 'Alimentación',
                'fecha' => Carbon::parse('2025-09-03'),
                'numero_factura' => 'FAC-002',
                'importe' => 150.00,
                'descripcion' => 'Desayuno y almuerzo del conductor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ruta_id' => 2,
                'nombre_servicio' => 'Hospedaje',
                'fecha' => Carbon::parse('2025-09-11'),
                'numero_factura' => 'FAC-010',
                'importe' => 300.00,
                'descripcion' => 'Hotel en Piura por 2 noches',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
