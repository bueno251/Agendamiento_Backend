<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ClienteTipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = 'INSERT INTO cliente_tipo_documento
        (tipo, created_at)
        VALUES (?, now())';

        DB::insert($query, ['Tarjeta de identidad']);
        DB::insert($query, ['Cedula ciudadanía']);
    }
}
