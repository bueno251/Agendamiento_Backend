<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        (tipo, created_at)
        VALUES (?, now())';

        DB::insert($query, ['persona natural']);
        DB::insert($query, ['persona jurídica']);
    }
}
