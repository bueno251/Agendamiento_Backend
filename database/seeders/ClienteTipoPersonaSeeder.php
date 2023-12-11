<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ClienteTipoPersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = 'INSERT INTO cliente_tipo_persona
        (tipo)
        VALUES (?)';

        DB::insert($query, ['Persona natural']);
        DB::insert($query, ['Persona jurídica']);
    }
}
