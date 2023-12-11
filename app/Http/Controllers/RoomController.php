<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\room;

class RoomController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'roomTipo' => 'required',
            'capacidad' => 'required',
            'estado' => 'required',
        ]);

        $query = 'INSERT INTO rooms
        (nombre,
        descripcion,
        room_tipo_id,
        capacidad,
        room_estado_id,
        created_at)
        VALUES (?, ?, ?, ?, ?, now())';

        $room = DB::insert($query, [
            $request->nombre,
            $request->descripcion,
            $request->roomTipo,
            $request->capacidad,
            $request->estado,
        ]);

        if ($room) {
            return response()->json([
                'message' => 'Habitación creada exitosamente',
            ]);
        } else {
            return response()->json([
                'message' => 'Error al crear',
            ], 500);
        }
    }

    public function read()
    {
        $query = 'SELECT
        r.id AS id,
        r.nombre AS nombre,
        r.descripcion AS descripcion,
        r.room_tipo_id AS tipoId,
        rt.tipo AS tipo,
        r.room_estado_id AS estadoId,
        re.estado AS estado,
        r.capacidad AS capacidad,
        r.habilitada AS habilitada
        FROM rooms r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        JOIN room_estados re ON r.room_estado_id = re.id
        WHERE r.deleted_at IS NULL
        ORDER BY r.created_at DESC';

        $rooms = DB::select($query);

        foreach ($rooms as $room) {
            $room->habilitada = $room->habilitada ? true : false;
        }

        return response($rooms, 200);
    }

    public function find($id)
    {
        $query = 'SELECT
        r.id AS id,
        r.nombre AS nombre,
        r.descripcion AS descripcion,
        r.room_tipo_id AS tipoId,
        rt.tipo AS tipo,
        r.room_estado_id AS estadoId,
        re.estado AS estado,
        r.capacidad AS capacidad
        FROM rooms r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        JOIN room_estados re ON r.room_estado_id = re.id
        WHERE r.id = ? && r.deleted_at IS NULL';

        $rooms = DB::select($query, [$id]);

        return response($rooms, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'roomTipo' => 'required',
            'capacidad' => 'required',
            'estado' => 'required',
        ]);

        $query = 'UPDATE rooms SET
        nombre = ?,
        descripcion = ?,
        room_tipo_id = ?,
        capacidad = ?,
        room_estado_id = ?,
        updated_at = now()
        WHERE id = ?';

        $room = DB::update($query, [
            $request->nombre,
            $request->descripcion,
            $request->roomTipo,
            $request->capacidad,
            $request->estado,
            $id
        ]);

        if ($room) {
            return response()->json([
                'message' => 'Actualizada exitosamente',
            ]);
        } else {
            return response()->json([
                'message' => 'Error al actualizar',
            ], 500);
        }
    }

    public function delete($id)
    {
        $query = 'UPDATE rooms SET 
        deleted_at = now()
        WHERE id = ?';

        $room = DB::update($query, [
            $id
        ]);

        if ($room) {
            return response()->json([
                'message' => 'Eliminada exitosamente',
            ]);
        } else {
            return response()->json([
                'message' => 'Error al actualizar',
            ], 500);
        }
    }
}
