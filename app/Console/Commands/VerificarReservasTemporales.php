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
     * 
     * Procesar Reservas Temporales.
     *
     * Este método se encarga de procesar las reservas temporales que han estado activas por al menos 10 minutos.
     * Se mueven las reservas temporales verificadas de la tabla 'reservas_temporales' a la tabla 'reservas' y se eliminan de 'reservas_temporales'.
     */
    public function handle()
    {
        // Consulta para seleccionar reservas temporales que han estado activas por al menos 10 minutos
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
            AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) >= 10
        ORDER BY created_at ASC';

        // Obtener las reservas temporales que cumplen con los criterios
        $reservasTemporales = DB::select($query);

        // Consulta para insertar una reserva en la tabla 'reservas'
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

        // Consulta para eliminar una reserva temporal de la tabla 'reservas_temporales'
        $queryDelete = 'UPDATE reservas_temporales SET 
        deleted_at = NOW()
        WHERE id = ?';

        // Procesar cada reserva temporal
        foreach ($reservasTemporales as $reserva) {
            // Verificar si la reserva tiene verificación de pago
            if ($reserva->verificacion_pago == 1) {
                // Insertar la reserva en la tabla 'reservas'
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

            // Eliminar la reserva temporal de 'reservas_temporales'
            DB::update($queryDelete, [
                $reserva->id
            ]);
        }
    }
}
