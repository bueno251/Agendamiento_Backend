<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CuponPrecioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nombre' => 'Alojamiento'],
            ['nombre' => 'Desayuno'],
            ['nombre' => 'Decoración'],
            ['nombre' => 'Adulto Adicional'],
            ['nombre' => 'Niño Adicional'],
            ['nombre' => 'Todo'],
        ];

        DB::table('tarifa_descuento_precios')->insert($data);
    }
}
