<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DireccionDepartamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sql = file_get_contents(database_path('seeders/sql/direcciones_departamentos.sql'));
        DB::unprepared($sql);
    }
}
