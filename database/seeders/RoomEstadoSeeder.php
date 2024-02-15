<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RoomEstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['estado' => 'Activo'],
            ['estado' => 'Inactivo'],
            ['estado' => 'Mantenimiento'],
        ];

        DB::table('room_estados')->insert($data);
    }
}
