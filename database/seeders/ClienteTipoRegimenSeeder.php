<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ClienteTipoRegimenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['tipo' => 'No responsable de IVA'],
            ['tipo' => 'Responsable de IVA'],
        ];

        DB::table('cliente_tipo_regimen')->insert($data);
    }
}
