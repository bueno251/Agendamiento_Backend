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
        $query = 'INSERT INTO room_tipos
        (tipo)
        VALUES (?)';

        DB::insert($query, ['Apartamento']);
        DB::insert($query, ['Cabaña']);
        DB::insert($query, ['Cuádruple']);
        DB::insert($query, ['Doble']);
        DB::insert($query, ['Domo']);
        DB::insert($query, ['Dormitorio Compartido']);
        DB::insert($query, ['Familiar']);
        DB::insert($query, ['Habitacion']);
        DB::insert($query, ['Sencilla']);
        DB::insert($query, ['Suite']);
        DB::insert($query, ['Twin']);
        DB::insert($query, ['Triple']);
        DB::insert($query, ['Otro']);
    }
}
