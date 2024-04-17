<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ReservasOrigenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nombre' => 'Página Web'],
            ['nombre' => 'Programa Interno'],
            ['nombre' => 'OTAS'],
        ];

        DB::table('reservas_origen')->insert($data);
    }
}
