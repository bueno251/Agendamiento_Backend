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
            [
                'nombre' => 'Transferencia',
                'requiere_comprobante' => 1,
            ],
            [
                'nombre' => 'Pasarela de pago',
                'requiere_comprobante' => 0,
            ],
        ];

        DB::table('reserva_metodo_pagos')->insert($data);
    }
}
