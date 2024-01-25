<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'roomTipo' => 'required|integer',
            'capacidad' => 'required|integer',
            'estado' => 'required|integer',
        ]);

        $query = 'INSERT INTO rooms (
        nombre,
        descripcion,
        room_tipo_id,
        capacidad,
        room_estado_id,
        created_at)
        VALUES (?, ?, ?, ?, ?, now())';

        $queryImagenes = 'INSERT INTO room_imgs (
        room_id,
        url,
        created_at)
        VALUES (?, ?, NOW())';

        DB::beginTransaction();

        try {
            DB::insert($query, [
                $request->nombre,
                $request->descripcion,
                $request->roomTipo,
                $request->capacidad,
                $request->estado,
            ]);

            $roomId = DB::getPdo()->lastInsertId();

            if ($request->hasFile('imgs')) {
                $images = $request->file('imgs');

                foreach ($images as $image) {
                    $ruta = $image->store('imgs', 'public');
                    DB::insert($queryImagenes, [
                        $roomId,
                        $ruta,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Habitación creada exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear',
                'error' => $e->getMessage(),
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
        r.habilitada AS habilitada,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", ri.id, "url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_id = r.id AND ri.deleted_at IS NULL
        ) AS imgs
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
        r.habilitada AS habilitada,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", ri.id, "url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_id = r.id AND ri.deleted_at IS NULL
        ) AS imgs
        FROM rooms r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        JOIN room_estados re ON r.room_estado_id = re.id
        WHERE r.id = ? && r.deleted_at IS NULL';

        $rooms = DB::select($query, [$id]);

        if (count($rooms) == 0) {
            return response()->json([
                'message' => 'Habitacion inexistente',
            ], 500);
        }

        $rooms[0]->habilitada = $rooms[0]->habilitada ? true : false;
        $rooms[0]->precios = $this->getPrecios($rooms[0]->id);

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
        r.habilitada AS habilitada,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", ri.id, "url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_id = r.id AND ri.deleted_at IS NULL
        ) AS imgs
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
                            'precio' => 'required|integer',
                            'jornada_id' => 'required|integer',
                        ]);

                        if ($validate->fails()) {
                            $fail('el formato de los dias es incorrecto: { name:string, precio:integer, jornada_id:integer }');
                            break;
                        }
                    }
                }
            ],
        ]);

        $weekdays = $request->input("weekdays");

        DB::beginTransaction();

        try {

            $query = 'INSERT INTO room_tarifas (
            room_id,
            dia_semana,
            precio,
            jornada_id,
            created_at)
            VALUES (?, ?, ?, ?, now())
            ON DUPLICATE KEY UPDATE
            precio = VALUES(precio),
            jornada_id = VALUES(jornada_id),
            updated_at = NOW()';

            foreach ($weekdays as $day) {
                DB::insert($query, [
                    $id,
                    $day['name'],
                    $day['precio'],
                    $day['jornada_id'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Precios guradados exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error inesperado al guardar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPrecios(int $id)
    {
        $query = 'SELECT
        rt.dia_semana AS name,
        rt.precio AS precio,
        tj.nombre AS jornada,
        rt.jornada_id AS jornada_id,
        rt.created_at AS created_at
        FROM room_tarifas rt
        JOIN tarifa_jornada tj ON tj.id = rt.jornada_id
        WHERE rt.room_id = ? AND rt.deleted_at IS NULL
        ORDER BY
        FIELD(rt.dia_semana, "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado")';

        $roomprecios = DB::select($query, [$id]);

        return $roomprecios;
    }

    public function getJornadas()
    {
        $query = 'SELECT
        id,
        nombre
        FROM tarifa_jornada';

        $tarifas = DB::select($query);

        return $tarifas;
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

    public function updateImg(Request $request, $id)
    {
        $queryImagenes = 'INSERT INTO room_imgs (
        room_id,
        url,
        created_at)
        VALUES (?, ?, NOW())';

        $queryDelImgs = 'UPDATE room_imgs SET 
        deleted_at = now()
        WHERE id = ?';

        DB::beginTransaction();

        try {

            if ($request->hasFile('imgs')) {
                $images = $request->file('imgs');

                foreach ($images as $image) {
                    $ruta = $image->store('imgs', 'public');
                    DB::insert($queryImagenes, [
                        $id,
                        $ruta,
                    ]);
                }
            }

            $toDelete = $request->input('toDelete', []);

            foreach ($toDelete as $imgID) {
                DB::update($queryDelImgs, [$imgID]);
            }

            DB::commit();

            $urls = $request->input('urls', []);

            foreach ($urls as $url) {
                $filePath = public_path('storage/' . $url);
                
                // Verificar si el archivo existe antes de intentar eliminarlo
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            return response()->json([
                'message' => 'Imagenes Guardadas',
                'urls' => $urls
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear',
                'error' => $e->getMessage(),
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
