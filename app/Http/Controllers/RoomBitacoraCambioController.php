<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class RoomBitacoraCambioController extends Controller
{
    public static function create(int $user, int $room, int $estadoNew, int $estadoOld, string $motivo = 'Ninguno')
    {
        $query = 'INSERT INTO room_bitacora_cambios (
        user_id,
        room_id,
        estado_nuevo_id,
        estado_anterior_id,
        motivo,
        created_at)
        VALUES (?, ?, ?, ?, ?, now())';

        DB::insert($query, [
            $user,
            $room,
            $estadoNew,
            $estadoOld,
            $motivo,
        ]);
    }

    public function read($id)
    {
        $query = 'SELECT
        rbc.room_id AS room,
        rbc.user_id AS user,
        new.estado AS estadoNuevo,
        old.estado AS estadoAntiguo,
        rbc.created_at AS created_at
        FROM room_bitacora_cambios rbc
        JOIN room_estados new ON rbc.estado_nuevo_id = new.id
        JOIN room_estados old ON rbc.estado_anterior_id = old.id
        WHERE rbc.room_id = ? && rbc.deleted_at IS NULL
        ORDER BY rbc.created_at DESC';

        $cambios = DB::select($query, [
            $id
        ]);

        foreach ($cambios as $cambio) {
            $cambio->user = UserController::getUser($cambio->user);
            $cambio->room = RoomController::getRoom($cambio->room);
        }

        return response($cambios, 200);
    }
}
