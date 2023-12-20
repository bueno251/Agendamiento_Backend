<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $query = 'INSERT INTO rooms (
        nombre,
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
        r.capacidad AS capacidad,
        r.habilitada AS habilitada
        FROM rooms r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        JOIN room_estados re ON r.room_estado_id = re.id
        WHERE r.id = ? && r.deleted_at IS NULL';

        $rooms = DB::select($query, [$id]);

        $rooms[0]->habilitada = $rooms[0]->habilitada ? true : false;


        return response($rooms, 200);
    }

    public static function getRoom(int $id)
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
        WHERE r.id = ? && r.deleted_at IS NULL';

        $rooms = DB::select($query, [
            $id
        ]);

        if (count($rooms) > 0) {
            $rooms[0]->habilitada = $rooms[0]->habilitada ? true : false;

            return $rooms[0];
        } else {
            return null;
        }
    }

    public function savePrecios(Request $request, int $id)
    {
        $request->validate([
            'weekdays' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $pago) {
                        $validate = validator($pago, [
                            'name' => 'required|string',
                            'normal' => 'required|integer',
                            'festivo' => 'required|integer',
                        ]);

                        if ($validate->fails()) {
                            $fail('el formato de los dias es incorrecto: { name:string, normal:integer, festivo:integer }');
                            break;
                        }
                    }
                }
            ],
        ]);

        $weekdays = $request->input("weekdays");

        try {

            $query = 'INSERT INTO room_tarifas (
            room_id,
            dia_semana,
            precio,
            precio_festivo,
            created_at)
            VALUES (?, ?, ?, ?, now())
            ON DUPLICATE KEY UPDATE
            precio = VALUES(precio),
            precio_festivo = VALUES(precio_festivo),
            updated_at = NOW()';

            foreach ($weekdays as $day) {
                DB::insert($query, [
                    $id,
                    $day['name'],
                    $day['normal'],
                    $day['festivo'],
                ]);
            }

            return response()->json([
                'message' => 'Precios guradados exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al guardar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getPrecios(int $id)
    {
        $query = 'SELECT
        rt.id AS id,
        rt.room_id AS room,
        rt.dia_semana AS name,
        rt.precio AS normal,
        rt.precio_festivo AS festivo,
        rt.created_at AS created_at
        FROM room_tarifas rt
        WHERE rt.room_id = ? && rt.deleted_at IS NULL
        ORDER BY rt.id ASC';

        $roomprecios = DB::select($query, [$id]);

        return response($roomprecios, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user' => 'required',
            'nombre' => 'required',
            'descripcion' => 'required',
            'roomTipo' => 'required',
            'capacidad' => 'required',
            'estado' => 'required',
            'estadoAntiguo' => 'required',
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

        RoomBitacoraCambioController::create($request->user, $id, $request->estado, $request->estadoAntiguo);

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
