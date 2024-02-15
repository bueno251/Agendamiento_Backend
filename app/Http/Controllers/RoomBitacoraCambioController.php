<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class RoomBitacoraCambioController extends Controller
{
    /**
     * Crear Registro en la Bitácora de Cambios de Habitaciones
     *
     * Este método se encarga de crear un nuevo registro en la bitácora de cambios de habitaciones.
     *
     * @param int $user ID del usuario que realiza el cambio.
     * @param int $room ID de la habitación afectada.
     * @param int $estadoNew ID del nuevo estado de la habitación.
     * @param int $estadoOld ID del estado anterior de la habitación.
     * @param string $motivo Motivo del cambio (opcional, por defecto: 'Ninguno').
     * @return void
     * @throws \Exception Si hay un problema al guardar el registro.
     */
    public static function create(int $user, int $room, int $estadoNew, int $estadoOld, string $motivo = 'Ninguno')
    {
        try {
            // Consulta SQL para insertar un nuevo registro en la bitácora de cambios
            $query = 'INSERT INTO room_bitacora_cambios (
            user_id,
            room_id,
            estado_nuevo_id,
            estado_anterior_id,
            motivo,
            created_at)
            VALUES (?, ?, ?, ?, ?, NOW())';

            // Ejecutar la inserción del registro en la bitácora de cambios
            $inserted = DB::insert($query, [
                $user,
                $room,
                $estadoNew,
                $estadoOld,
                $motivo,
            ]);

            if (!$inserted) {
                throw new \Exception('Hubo un problema al guardar el registro en la bitácora de cambios.');
            }
        } catch (\Exception $e) {
            // Lanzar la excepción original para que pueda ser manejada en el código que llama a esta función
            throw $e;
        }
    }

    /**
     * Obtener Historial de Cambios de una Habitación
     *
     * Este método se encarga de obtener el historial de cambios de estado de una habitación desde la bitácora.
     *
     * @param int $id ID de la habitación cuyo historial se va a obtener.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el historial de cambios de la habitación.
     */
    public function read($id)
    {
        try {
            $query = 'SELECT
            (
                SELECT
                JSON_OBJECT("id", u.id, "nombre", u.nombre, "correo", u.correo)
                FROM users u
                WHERE u.id = rbc.user_id AND u.deleted_at IS NULL
                LIMIT 1
            ) AS user,
            new.estado AS estadoNuevo,
            old.estado AS estadoAntiguo,
            rbc.created_at AS created_at
            FROM room_bitacora_cambios rbc
            JOIN room_estados new ON rbc.estado_nuevo_id = new.id
            JOIN room_estados old ON rbc.estado_anterior_id = old.id
            WHERE rbc.room_id = ? AND rbc.deleted_at IS NULL
            ORDER BY rbc.created_at DESC';

            $cambios = DB::select($query, [$id]);

            foreach ($cambios as $cambio) {
                // Obtener detalles del usuario para cada cambio
                $cambio->user = json_decode($cambio->user);
            }

            return response()->json($cambios, 200);
        } catch (\Exception $e) {
            // Manejar el error de manera adecuada (puedes loggearlo o realizar alguna acción específica)
            return response()->json([
                'message' => 'Error al obtener el historial de cambios de la habitación.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
