<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'usuario_reserva' => 1,
            'correo_obligatorio' => 1,
            'porcentaje_separacion' => 0,
            'created_at' => now(),
        ];

        DB::table('configuracions')->insert($data);
    }
}
