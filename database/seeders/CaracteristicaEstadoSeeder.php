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
        $query = "INSERT INTO room_caracteristica_estados
        (estado)
        VALUES (?)";

        DB::insert($query, ['Activo']);
        DB::insert($query, ['Inactivo']);
    }
}
