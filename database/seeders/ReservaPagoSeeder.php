<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ReservaPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['tipo' => 'Transferencia'],
            ['tipo' => 'Pasarela de pago'],
        ];

        DB::table('reserva_metodo_pagos')->insert($data);
    }
}
