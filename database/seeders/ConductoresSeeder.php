<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConductoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('conductores')->insert([
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'fecha_nacimiento' => '1985-06-15',
                'genero' => 'Masculino',
                'licencia' => 'A1234567',
                'tipo_licencia' => 'A',
                'telefono' => '987654321',
                'email' => 'juan.perez@example.com',
                'direccion' => 'Av. Los Olivos 123',
                'ciudad' => 'Lima',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'María',
                'apellido' => 'Ramírez',
                'fecha_nacimiento' => '1990-09-21',
                'genero' => 'Femenino',
                'licencia' => 'B7654321',
                'tipo_licencia' => 'B',
                'telefono' => '912345678',
                'email' => 'maria.ramirez@example.com',
                'direccion' => 'Jr. Primavera 456',
                'ciudad' => 'Trujillo',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Gómez',
                'fecha_nacimiento' => '1982-03-10',
                'genero' => 'Masculino',
                'licencia' => 'C4567890',
                'tipo_licencia' => 'C',
                'telefono' => '956123789',
                'email' => 'carlos.gomez@example.com',
                'direccion' => 'Mz. A Lt. 12 Urb. El Sol',
                'ciudad' => 'Arequipa',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'Fernández',
                'fecha_nacimiento' => '1995-12-02',
                'genero' => 'Femenino',
                'licencia' => 'D9876543',
                'tipo_licencia' => 'D',
                'telefono' => '954789632',
                'email' => 'ana.fernandez@example.com',
                'direccion' => 'Av. Central 890',
                'ciudad' => 'Piura',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
