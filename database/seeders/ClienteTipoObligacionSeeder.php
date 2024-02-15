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
        $data = [
            ['tipo' => 'Agente de retención en el impuesto sobre las ventas'],
            ['tipo' => 'Autoretenedor'],
            ['tipo' => 'Gran contribuyente'],
            ['tipo' => 'No responsable'],
            ['tipo' => 'Responsable'],
            ['tipo' => 'Régimen simple de tributación - SIMPLE'],
        ];

        DB::table('cliente_tipo_obligacion')->insert($data);
    }
}
