<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nombre' => 'Wifi',
                'url_icon' => 'wifi',
                'descripcion' => 'Servicio de internet',
            ],
            [
                'nombre' => 'Desayuno',
                'url_icon' => 'coffee',
                'descripcion' => 'Servicio de desayuno',
            ],
            [
                'nombre' => 'Baño',
                'url_icon' => 'toilet',
                'descripcion' => 'Servicio de baño',
            ],
            [
                'nombre' => 'Jacuzzi',
                'url_icon' => 'pot-steam',
                'descripcion' => 'Servicio de jacuzzi',
            ],
        ];

        DB::table('room_caracteristicas')->insert($data);
    }
}
