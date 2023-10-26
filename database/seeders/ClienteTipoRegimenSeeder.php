<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ClienteTipoRegimenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = 'INSERT INTO cliente_tipo_regimen
        (tipo, created_at)
        VALUES (?, now())';

        DB::insert($query, ['no responsable de IVA']);
        DB::insert($query, ['responsable de IVA']);
    }
}
