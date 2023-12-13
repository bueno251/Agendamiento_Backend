<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ClienteTipoObligacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = 'INSERT INTO cliente_tipo_obligacion
        (tipo)
        VALUES (?)';

        DB::insert($query, ['Agente de retención en el impuesto sobre las ventas']);
        DB::insert($query, ['Autoretenedor']);
        DB::insert($query, ['Gran contribuyente']);
        DB::insert($query, ['No responsable']);
        DB::insert($query, ['Responsable']);
        DB::insert($query, ['Régimen simple de tributación - SIMPLE']);
    }
}
