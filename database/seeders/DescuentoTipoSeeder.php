<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DescuentoTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['tipo' => 'Porcentaje'],
            ['tipo' => 'Precio'],
        ];

        DB::table('tarifa_descuento_tipos')->insert($data);
    }
}
