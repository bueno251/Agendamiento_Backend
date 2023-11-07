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
        $query = 'INSERT INTO configuracions
        (usuario_reserva, created_at)
        VALUES (?, now())';

        DB::insert($query, [1]);
    }
}