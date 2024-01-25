<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ClienteTipoDocumentoSeeder::class,
            ClienteTipoObligacionSeeder::class,
            ClienteTipoPersonaSeeder::class,
            ClienteTipoRegimenSeeder::class,
            ConfiguracionSeeder::class,
            EmpresaEntornoSeeder::class,
            EmpresaOperacionSeeder::class,
            RoomEstadoSeeder::class,
            RoomTipoSeeder::class,
            ReservaEstadoSeeder::class,
            ReservaPagoSeeder::class,
            TarifaJornadaSeeder::class,
            UserSeeder::class,
            ConfiguracionPagoSeeder::class,
        ]);
    }
}
