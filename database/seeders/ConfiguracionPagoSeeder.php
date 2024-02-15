<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ConfiguracionPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'configuracion_id' => 1,
                'reserva_tipo_pago_id' => 1,
                'estado' => 1,
                'created_at' => now(),
            ],
            [
                'configuracion_id' => 1,
                'reserva_tipo_pago_id' => 2,
                'estado' => 0,
                'created_at' => now(),
            ],
        ];

        DB::table('configuracion_pagos')->insert($data);
    }
}
