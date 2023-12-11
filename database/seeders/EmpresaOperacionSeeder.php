<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class EmpresaOperacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = 'INSERT INTO empresa_tipo_operacion (tipo) VALUES (?)';

        DB::insert($query, ['Estandar']);
    }
}
