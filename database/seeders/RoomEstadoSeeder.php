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
        $query = 'INSERT INTO room_estados
        (estado, created_at)
        VALUES (?, now())';

        DB::insert($query, ['Activo']);
        DB::insert($query, ['Inactivo']);
        DB::insert($query, ['Mantenimiento']);
    }
}
