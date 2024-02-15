<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RoomTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['tipo' => 'Apartamento'],
            ['tipo' => 'Cabaña'],
            ['tipo' => 'Cuádruple'],
            ['tipo' => 'Doble'],
            ['tipo' => 'Domo'],
            ['tipo' => 'Dormitorio Compartido'],
            ['tipo' => 'Familiar'],
            ['tipo' => 'Habitacion'],
            ['tipo' => 'Sencilla'],
            ['tipo' => 'Suite'],
            ['tipo' => 'Twin'],
            ['tipo' => 'Triple'],
            ['tipo' => 'Otro'],
        ];

        DB::table('room_tipos')->insert($data);
    }
}
