<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'usuario_reserva' => 1,
            'created_at' => now(),
        ];

        DB::table('configuracions')->insert($data);
    }
}
