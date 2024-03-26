<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DivisasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $data = [
            [
                'nombre' => 'Peso Colombiano',
                'codigo' => 'COP',
            ],
        ];

        DB::table('tarifas_divisas')->insert($data);
    }
}
