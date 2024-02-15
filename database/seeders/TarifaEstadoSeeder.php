<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TarifaEstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['estado' => 'Activo'],
            ['estado' => 'Inactivo'],
        ];

        DB::table('tarifa_estados')->insert($data);
    }
}
