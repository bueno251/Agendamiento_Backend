<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ReservaEstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['estado' => 'Pendiente'],
            ['estado' => 'Confirmada'],
            ['estado' => 'Rechazada'],
            ['estado' => 'Cancelada'],
        ];

        DB::table('reserva_estados')->insert($data);
    }
}
