<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerificarReservasTemporales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservasTemporales:verificar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica las reservas temporales y las elimina si no se han confirmado en 5 minutos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = 'SELECT id,
        fecha_entrada,
        fecha_salida,
        room_id,
        cliente_id,
        user_id,
        estado_id,
        desayuno_id,
        decoracion_id,
        huespedes,
        adultos,
        niños,
        precio,
        abono,
        comprobante,
        verificacion_pago
        FROM reservas_temporales
        WHERE
            deleted_at IS NULL
            AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) >= 5
        ORDER BY created_at ASC';

        $reservasTemporales = DB::select($query);

        $queryInsert = 'INSERT INTO reservas (
        fecha_entrada,
        fecha_salida,
        room_id,
        cliente_id,
        user_id,
        estado_id,
        desayuno_id,
        decoracion_id,
        huespedes,
        adultos,
        niños,
        precio,
        abono,
        comprobante,
        verificacion_pago,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        $queryDelete = 'UPDATE reservas_temporales SET 
        deleted_at = NOW()
        WHERE id = ?';

        foreach ($reservasTemporales as $reserva) {
            if ($reserva->verificacion_pago == 1) {
                DB::insert($queryInsert, [
                    $reserva->fecha_entrada,
                    $reserva->fecha_salida,
                    $reserva->room_id,
                    $reserva->cliente_id,
                    $reserva->user_id,
                    $reserva->estado_id,
                    $reserva->desayuno_id,
                    $reserva->decoracion_id,
                    $reserva->huespedes,
                    $reserva->adultos,
                    $reserva->niños,
                    $reserva->precio,
                    $reserva->abono,
                    $reserva->comprobante,
                    $reserva->verificacion_pago
                ]);
            }

            DB::update($queryDelete, [
                $reserva->id
            ]);
        }
    }
}
