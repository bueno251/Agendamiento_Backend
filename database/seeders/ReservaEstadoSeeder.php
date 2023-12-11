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
        $query = 'INSERT INTO reserva_estados
        (estado)
        VALUES (?)';

        DB::insert($query, ['Pendiente']);
        DB::insert($query, ['Confirmada']);
        DB::insert($query, ['Cancelada']);
    }
}
