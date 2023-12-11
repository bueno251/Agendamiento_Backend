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
        $query = 'INSERT INTO reserva_tipo_pagos
        (tipo)
        VALUES (?)';

        DB::insert($query, ['Tranfererencia']);
        DB::insert($query, ['Pasarela de pago']);
    }
}
