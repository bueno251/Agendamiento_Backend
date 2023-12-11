<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ClienteTipoObligacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = 'INSERT INTO cliente_tipo_obligacion
        (tipo)
        VALUES (?)';

        DB::insert($query, ['No responsable']);
        DB::insert($query, ['Responsable']);
    }
}
