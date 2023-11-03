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
        $query = 'INSERT INTO cliente_tipo_regimen
        (tipo, created_at)
        VALUES (?, now())';

        DB::insert($query, ['No responsable de IVA']);
        DB::insert($query, ['Responsable de IVA']);
    }
}
