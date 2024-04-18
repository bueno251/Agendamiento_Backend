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
            'calendario_inhabilitado' => 0,
            'correo_obligatorio' => 1,
            'extrangeros_pagan_impuestos' => 1,
            'porcentaje_separacion' => 0,
            'tarifas_generales' => 0,
            'ventas_otas' => 0,
            'edad_tarifa_niños' => 2,
            'created_at' => now(),
        ];

        DB::table('configuracions')->insert($data);
    }
}
