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
            CancelacionTipoSeeder::class,
            CaracteristicaEstadoSeeder::class,
            ClienteTipoDocumentoSeeder::class,
            ClienteTipoObligacionSeeder::class,
            ClienteTipoPersonaSeeder::class,
            ClienteTipoRegimenSeeder::class,
            ConfiguracionSeeder::class,
            CuponPrecioSeeder::class,
            DescuentoTipoSeeder::class,
            DivisasSeeder::class,
            EmpresaEntornoSeeder::class,
            EmpresaOperacionSeeder::class,
            RoomEstadoSeeder::class,
            RoomTipoSeeder::class,
            ReservaEstadoSeeder::class,
            ReservaMotivosSeeder::class,
            ReservaPagoSeeder::class,
            ReprogramacionMotivosSeeder::class,
            TarifaJornadaSeeder::class,
            TarifaEstadoSeeder::class,
            UserSeeder::class,
            ConfiguracionPagoSeeder::class,
        ]);
    }
}
