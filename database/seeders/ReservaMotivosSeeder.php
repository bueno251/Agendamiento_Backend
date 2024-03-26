<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ReservaMotivosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nombre' => 'Entretenimiento'],
            ['nombre' => 'Vacaciones'],
            ['nombre' => 'Trabajo'],
            ['nombre' => 'Otros'],
        ];

        DB::table('reserva_motivos')->insert($data);
    }
}
