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
        $data = [
            ['tipo' => 'Persona natural'],
            ['tipo' => 'Persona jurídica'],
        ];

        DB::table('cliente_tipo_persona')->insert($data);
    }
}
