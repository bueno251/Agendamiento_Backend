<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CancelacionTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['tipo' => 'Problemas Personales'],
            ['tipo' => 'Problemas Con El Transporte'],
        ];

        DB::table('reservas_cancelacion_tipos')->insert($data);
    }
}
