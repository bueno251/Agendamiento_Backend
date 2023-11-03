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
        (tipo, created_at)
        VALUES (?, now())';

        DB::insert($query, ['Cabaña']);
        DB::insert($query, ['Habitacion']);
    }
}
