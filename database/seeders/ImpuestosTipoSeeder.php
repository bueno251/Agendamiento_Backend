<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ImpuestosTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['tipo' => 'Porcentaje'],
            ['tipo' => 'Precio'],
        ];

        DB::table('tarifas_impuesto_tipos')->insert($data);
    }
}
