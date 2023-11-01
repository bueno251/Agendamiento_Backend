<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TipoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = 'INSERT INTO tipo_pagos
        (tipo, created_at)
        VALUES (?, now())';

        DB::insert($query, ['tranfererencia']);
        DB::insert($query, ['pasarela de pago']);
    }
}
