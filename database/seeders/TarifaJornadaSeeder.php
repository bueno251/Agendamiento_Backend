<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TarifaJornadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = 'INSERT INTO tarifa_jornada
        (nombre)
        VALUES (?)';

        DB::insert($query, [
            'Semana'
        ]);

        DB::insert($query, [
            'Fin de semana'
        ]);
    }
}
