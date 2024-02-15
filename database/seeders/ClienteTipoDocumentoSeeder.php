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
        $data = [
            ['tipo' => 'Cedula ciudadanía'],
            ['tipo' => 'Cédula de extranjería'],
            ['tipo' => 'Documento de identificación extranjero'],
            ['tipo' => 'NIT'],
            ['tipo' => 'NIT de otro país'],
            ['tipo' => 'NUIP'],
            ['tipo' => 'Pasaporte'],
            ['tipo' => 'Registro civil'],
            ['tipo' => 'Tarjeta de extranjería'],
            ['tipo' => 'Tarjeta de identidad'],
        ];

        DB::table('cliente_tipo_documento')->insert($data);
    }
}
