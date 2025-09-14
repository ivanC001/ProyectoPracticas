<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class CaminonesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ---------------- CAMIONES ----------------
        DB::table('camiones')->insert([
            [
                'fecha_ingreso' => '2023-01-15',
                'placa_tracto' => 'ABC-123',
                'placa_carreto' => 'XYZ-987',
                'color' => 'Rojo',
                'mtc' => 'MTC-111',
                'foto_camino' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'fecha_ingreso' => '2023-02-20',
                'placa_tracto' => 'DEF-456',
                'placa_carreto' => 'UVW-654',
                'color' => 'Azul',
                'mtc' => 'MTC-222',
                'foto_camino' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'fecha_ingreso' => '2023-03-10',
                'placa_tracto' => 'GHI-789',
                'placa_carreto' => 'RST-321',
                'color' => 'Blanco',
                'mtc' => 'MTC-333',
                'foto_camino' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'fecha_ingreso' => '2023-04-05',
                'placa_tracto' => 'JKL-012',
                'placa_carreto' => 'OPQ-888',
                'color' => 'Negro',
                'mtc' => 'MTC-444',
                'foto_camino' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

    
    }
}
