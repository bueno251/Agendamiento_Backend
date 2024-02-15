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
        $data = [
            ['tipo' => 'Pruebas'],
            ['tipo' => 'Producción'],
        ];

        DB::table('empresa_tipo_entorno')->insert($data);
    }
}
