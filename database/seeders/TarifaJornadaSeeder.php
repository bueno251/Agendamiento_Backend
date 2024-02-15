<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TarifaJornadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nombre' => 'Semana'],
            ['nombre' => 'Fin de semana'],
        ];

        DB::table('tarifa_jornada')->insert($data);
    }
}
