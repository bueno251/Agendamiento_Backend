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
        (tipo)
        VALUES (?)';

        DB::insert($query, ['Cedula ciudadanía']);
        DB::insert($query, ['Cédula de extranjería']);
        DB::insert($query, ['Documento de identificación extranjero']);
        DB::insert($query, ['NIT']);
        DB::insert($query, ['NIT de otro país']);
        DB::insert($query, ['NUIP']);
        DB::insert($query, ['Pasaporte']);
        DB::insert($query, ['Registro civil']);
        DB::insert($query, ['Tarjeta de extranjería']);
        DB::insert($query, ['Tarjeta de identidad']);
    }
}
