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
        $query = 'INSERT INTO configuracion_pagos
        (configuracion_id,
        reserva_tipo_pago_id,
        estado,
        created_at)
        VALUES (?, ?, ?, now())';

        DB::insert($query, [1, 1, 1]);
        DB::insert($query, [1, 2, 0]);
    }
}
