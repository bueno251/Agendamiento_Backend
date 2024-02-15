<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CaracteristicaEstadoSeeder extends Seeder
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

        DB::table('room_caracteristica_estados')->insert($data);
    }
}
