<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    /**
     * Crear Habitación
     *
     * Este método se encarga de crear una nueva habitación en el sistema de gestión de habitaciones.
     * La información de la habitación se recibe a través de una solicitud HTTP, se valida y se realiza la inserción de datos en varias tablas de la base de datos.
     * Además, se realiza un manejo de transacciones para garantizar la integridad de los datos.
     *
     * @param Request $request Datos de entrada que incluyen información como 'nombre' (string, obligatorio), 'descripcion' (string, obligatorio), 'roomTipo' (integer, obligatorio), 'capacidad' (integer, obligatorio), 'estado' (integer, obligatorio), 'cantidad' (integer, obligatorio), 'desayuno' (integer, obligatorio), 'decoracion' (integer, obligatorio), 'imgs' (array de archivos, opcional), 'caracteristic' (array de enteros, opcional).
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'roomTipo' => 'required|integer',
            'capacidad' => 'required|integer',
            'estado' => 'required|integer',
            'cantidad' => 'required|integer',
            'desayuno' => 'required|integer',
            'decoracion' => 'required|integer',
        ]);

        $query = 'INSERT INTO room_padre (
        nombre,
        descripcion,
        room_tipo_id,
        capacidad,
        room_estado_id,
        cantidad,
        has_desayuno,
        has_decoracion,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, now())';

        $queryRooms = 'INSERT INTO rooms (
        room_padre_id,
        nombre,
        descripcion,
        room_tipo_id,
        capacidad,
        room_estado_id,
        has_desayuno,
        has_decoracion,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, now())';

        $queryImagenes = 'INSERT INTO room_imgs (
        room_padre_id,
        url,
        created_at)
        VALUES (?, ?, NOW())';

        $queryCaracteristicas = 'INSERT INTO room_caracteristica_relacion (
        room_id,
        caracteristica_id,
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
                $request->cantidad,
                $request->desayuno,
                $request->decoracion,
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

            for ($i = 0; $i < $request->cantidad; $i++) {
                DB::insert($queryRooms, [
                    $roomId,
                    $request->nombre . ' - ' . (1 + $i),
                    $request->descripcion,
                    $request->roomTipo,
                    $request->capacidad,
                    $request->estado,
                    $request->desayuno,
                    $request->decoracion,
                ]);
            }

            $caracteristics = $request->input('caracteristic', []);

            foreach ($caracteristics as $caracteristic) {
                DB::insert($queryCaracteristicas, [
                    $roomId,
                    $caracteristic,
                ]);
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
        r.has_desayuno AS has_desayuno,
        r.has_decoracion AS has_decoracion,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", ri.id, "url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_padre_id = r.id AND ri.deleted_at IS NULL
        ) AS imgs,
        (
            SELECT
            JSON_ARRAYAGG(rcr.caracteristica_id)
            FROM room_caracteristica_relacion rcr
            WHERE rcr.room_id = r.id AND rcr.estado = 1 AND rcr.deleted_at IS NULL
        ) AS caracteristics,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", rs.id,"nombre", rs.nombre, "estado_id", rs.room_estado_id))
            FROM rooms rs
            WHERE rs.room_padre_id = r.id AND rs.deleted_at IS NULL
        ) AS rooms
        FROM room_padre r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        JOIN room_estados re ON r.room_estado_id = re.id
        WHERE r.deleted_at IS NULL
        ORDER BY r.created_at DESC';

        $rooms = DB::select($query);

        foreach ($rooms as $room) {
            $room->habilitada = $room->habilitada ? true : false;
            $room->precios = $this->getPrecios($room->id);
            $room->imgs = json_decode($room->imgs);
            $room->caracteristics = json_decode($room->caracteristics);
            $room->rooms = json_decode($room->rooms);
        }

        return response($rooms, 200);
    }

    public function readClient()
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
        r.has_desayuno AS has_desayuno,
        r.has_decoracion AS has_decoracion,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", ri.id, "url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_padre_id = r.id AND ri.deleted_at IS NULL
        ) AS imgs,
        (
            SELECT
            JSON_ARRAYAGG(rcr.caracteristica_id)
            FROM room_caracteristica_relacion rcr
            WHERE rcr.room_id = r.id AND rcr.estado = 1 AND rcr.deleted_at IS NULL
        ) AS caracteristics
        FROM room_padre r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        JOIN room_estados re ON r.room_estado_id = re.id
        WHERE r.deleted_at IS NULL
        AND EXISTS (
            SELECT 1
            FROM room_tarifas rt
            WHERE rt.room_id = r.id AND rt.deleted_at IS NULL
        )
        ORDER BY r.created_at DESC';

        $rooms = DB::select($query);

        foreach ($rooms as $room) {
            $room->habilitada = $room->habilitada ? true : false;
            $room->precios = $this->getPrecios($room->id);
            $room->imgs = json_decode($room->imgs);
            $room->caracteristics = json_decode($room->caracteristics);
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
        r.cantidad AS cantidad,
        r.has_desayuno AS has_desayuno,
        r.has_decoracion AS has_decoracion,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", ri.id, "url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_padre_id = r.id AND ri.deleted_at IS NULL
        ) AS imgs,
        (
            SELECT
            JSON_ARRAYAGG(rcr.caracteristica_id)
            FROM room_caracteristica_relacion rcr
            WHERE rcr.room_id = r.id AND rcr.estado = 1 AND rcr.deleted_at IS NULL
        ) AS caracteristics,
        (
            SELECT
            COUNT(*)
            FROM rooms rs
            WHERE rs.room_padre_id = r.id AND rs.habilitada = 1 AND rs.deleted_at IS NULL
        ) AS rooms
        FROM room_padre r
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
        $rooms[0]->imgs = json_decode($rooms[0]->imgs);
        $rooms[0]->caracteristics = json_decode($rooms[0]->caracteristics);

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
        r.has_desayuno AS has_desayuno,
        r.has_decoracion AS has_decoracion,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", ri.id, "url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_padre_id = r.room_padre_id AND ri.deleted_at IS NULL
        ) AS imgs,
        (
            SELECT
            JSON_ARRAYAGG(rcr.caracteristica_id)
            FROM room_caracteristica_relacion rcr
            WHERE rcr.room_id = r.room_padre_id AND rcr.estado = 1 AND rcr.deleted_at IS NULL
        ) AS caracteristics
        FROM rooms r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        JOIN room_estados re ON r.room_estado_id = re.id
        WHERE r.id = ? && r.deleted_at IS NULL';

        $rooms = DB::select($query, [
            $id
        ]);

        if (count($rooms) > 0) {
            $rooms[0]->habilitada = $rooms[0]->habilitada ? true : false;
            $rooms[0]->imgs = json_decode($rooms[0]->imgs);
            $rooms[0]->caracteristics = json_decode($rooms[0]->caracteristics);

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
                        ]);

                        if ($validate->fails()) {
                            $fail('el formato de los dias es incorrecto: { name:string, precio:integer}');
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
                'message' => 'Precios guardados exitosamente',
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
        LEFT JOIN tarifa_jornada tj ON tj.id = rt.jornada_id
        WHERE rt.room_id = ? AND rt.deleted_at IS NULL
        ORDER BY
        FIELD(rt.dia_semana, "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Adicional", "Niños")';

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
            'desayuno' => 'required|integer',
            'decoracion' => 'required|integer',
        ]);

        $query = 'UPDATE room_padre SET
        nombre = ?,
        descripcion = ?,
        room_tipo_id = ?,
        capacidad = ?,
        room_estado_id = ?,
        has_desayuno = ?,
        has_decoracion = ?,
        updated_at = now()
        WHERE id = ?';

        $queryRooms = 'UPDATE rooms SET
        descripcion = ?,
        room_tipo_id = ?,
        capacidad = ?,
        room_estado_id = ?,
        has_desayuno = ?,
        has_decoracion = ?,
        updated_at = now()
        WHERE room_padre_id = ?';

        $queryCaracteristicasCreate = 'INSERT INTO room_caracteristica_relacion (
        room_id,
        caracteristica_id,
        estado,
        created_at) 
        VALUES (?, ?, 1, NOW()) 
        ON DUPLICATE KEY UPDATE estado = 1';

        $queryCaracteristicasUpdate = 'UPDATE room_caracteristica_relacion SET estado = 0, updated_at = NOW()
        WHERE room_id = ? AND caracteristica_id = ?';

        $activar = $request->input('activar');
        $desactivar = $request->input('desactivar');

        DB::beginTransaction();

        try {
            DB::update($query, [
                $request->nombre,
                $request->descripcion,
                $request->roomTipo,
                $request->capacidad,
                $request->estado,
                $request->desayuno,
                $request->decoracion,
                $id
            ]);

            DB::update($queryRooms, [
                $request->descripcion,
                $request->roomTipo,
                $request->capacidad,
                $request->estado,
                $request->desayuno,
                $request->decoracion,
                $id
            ]);

            RoomBitacoraCambioController::create($request->user, $id, $request->estado, $request->estadoAntiguo);

            foreach ($activar as $caracteristicaId) {
                DB::insert($queryCaracteristicasCreate, [$id, $caracteristicaId]);
            }

            foreach ($desactivar as $caracteristicaId) {
                DB::update($queryCaracteristicasUpdate, [$id, $caracteristicaId]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Actualizada exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateEstado(Request $request)
    {
        $request->validate([
            'rooms' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $pago) {
                        $validate = validator($pago, [
                            'id' => 'required|integer',
                            'estado_id' => 'required|integer',
                        ]);

                        if ($validate->fails()) {
                            $fail('el formato de las habitaciones es incorrecto. { id:integer, estado_id:integer }');
                            break;
                        }
                    }
                }
            ],
        ]);

        $query = 'UPDATE rooms SET
        room_estado_id = ?,
        updated_at = now()
        WHERE id = ?';

        $rooms = $request->input('rooms');

        DB::beginTransaction();

        try {
            foreach ($rooms as $room) {
                DB::update($query, [
                    $room['estado_id'],
                    $room['id'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Guardado Exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error Al Guardar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateImg(Request $request, $id)
    {
        $queryImagenes = 'INSERT INTO room_imgs (
        room_padre_id,
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
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error Al Guardar',
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
