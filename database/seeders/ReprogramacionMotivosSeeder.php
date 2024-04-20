<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ReprogramacionMotivosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nombre' => 'Permisos Laborales'],
            ['nombre' => 'Problemas De Transporte'],
            ['nombre' => 'Calamidad Doméstica'],
        ];

        DB::table('reservas_reprogramacion_motivos')->insert($data);
    }
}
