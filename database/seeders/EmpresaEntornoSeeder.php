<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class EmpresaEntornoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = 'INSERT INTO empresa_tipo_entorno (tipo) VALUES (?)';

        DB::insert($query, ['Pruebas']);
        DB::insert($query, ['Producción']);
    }
}
