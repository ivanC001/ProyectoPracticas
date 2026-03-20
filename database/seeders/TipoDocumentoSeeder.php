<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_documentos')->insert([
    ['codigo' => '0', 'descripcion' => 'SIN DOCUMENTO', 'longitud' => 0],
    ['codigo' => '1', 'descripcion' => 'DNI', 'longitud' => 8],
    ['codigo' => '6', 'descripcion' => 'RUC', 'longitud' => 11],
    ['codigo' => '4', 'descripcion' => 'CARNET EXTRANJERIA', 'longitud' => 12],
    ['codigo' => '7', 'descripcion' => 'PASAPORTE', 'longitud' => 12],
]);
    }
}
