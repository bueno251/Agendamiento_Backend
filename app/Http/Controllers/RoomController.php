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
        estado,
        created_at)
        VALUES (?, ?, ?, ?, ?, now())';

        DB::insert($query, [
            $request->nombre,
            $request->descripcion,
            $request->roomTipo,
            $request->capacidad,
            $request->estado,
        ]);

        return response('Habitacion Creada', 200);
    }

    public function read()
    {
        $query = 'SELECT
        r.id AS id,
        r.nombre AS nombre,
        r.descripcion AS descripcion,
        r.room_tipo_id AS tipoId,
        rt.tipo AS tipo,
        r.capacidad AS capacidad,
        r.estado AS estado
        FROM rooms r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        WHERE r.deleted_at IS NULL
        ORDER BY r.created_at DESC';

        $rooms = DB::select($query);

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
        r.capacidad AS capacidad,
        r.estado AS estado
        FROM rooms r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
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
        estado = ?,
        updated_at = now()
        WHERE id = ?';

        DB::update($query, [
            $request->nombre,
            $request->descripcion,
            $request->roomTipo,
            $request->capacidad,
            $request->estado,
            $id
        ]);

        return response('Habitacion Actualizada', 200);
    }

    public function delete($id)
    {
        $query = 'UPDATE rooms SET 
        deleted_at = now()
        WHERE id = ?';

        DB::update($query, [
            $id
        ]);

        return response("Eliminado", 200);
    }
}
