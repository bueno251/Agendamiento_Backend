<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    /**
     * Crear Habitación y Habitaciones Asociadas
     *
     * Este método se encarga de crear una habitación principal y las habitaciones asociadas.
     *
     * @param Request $request Datos de entrada que incluyen información sobre la habitación.
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
            'decoracion' => 'required|integer',
            'desayuno' => 'required|integer',
            'incluyeDesayuno' => 'required|integer',
        ]);

        $queryRoomPadre = 'INSERT INTO room_padre (
        nombre,
        descripcion,
        room_tipo_id,
        capacidad,
        room_estado_id,
        cantidad,
        has_decoracion,
        has_desayuno,
        incluye_desayuno,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        $queryRooms = 'INSERT INTO rooms (
        room_padre_id,
        nombre,
        descripcion,
        room_tipo_id,
        capacidad,
        room_estado_id,
        has_decoracion,
        has_desayuno,
        incluye_desayuno,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        $queryImages = 'INSERT INTO room_imgs (
        room_padre_id,
        url,
        created_at)
        VALUES (?, ?, NOW())';

        $queryCharacteristics = 'INSERT INTO room_caracteristica_relacion (
        room_id,
        caracteristica_id,
        created_at)
        VALUES (?, ?, NOW())';

        DB::beginTransaction();

        try {
            // Insertar la habitación principal
            DB::insert($queryRoomPadre, [
                $request->nombre,
                $request->descripcion,
                $request->roomTipo,
                $request->capacidad,
                $request->estado,
                $request->cantidad,
                $request->decoracion,
                $request->desayuno,
                $request->incluyeDesayuno,
            ]);
            
            // Obtener el ID de la habitación principal
            $roomId = DB::getPdo()->lastInsertId();
            
            // Insertar imágenes asociadas a la habitación principal
            if ($request->hasFile('imgs')) {
                $images = $request->file('imgs');
                
                foreach ($images as $image) {
                    $path = $image->store('imgs', 'public');
                    DB::insert($queryImages, [
                        $roomId,
                        $path,
                    ]);
                }
            }
            
            // Insertar habitaciones asociadas
            for ($i = 0; $i < $request->cantidad; $i++) {
                DB::insert($queryRooms, [
                    $roomId,
                    $request->nombre . ' - ' . (1 + $i),
                    $request->descripcion,
                    $request->roomTipo,
                    $request->capacidad,
                    $request->estado,
                    $request->decoracion,
                    $request->desayuno,
                    $request->incluyeDesayuno,
                ]);
            }

            // Insertar características asociadas a la habitación principal
            $characteristics = $request->input('caracteristic', []);

            foreach ($characteristics as $characteristic) {
                DB::insert($queryCharacteristics, [
                    $roomId,
                    $characteristic,
                ]);
            }

            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'message' => 'Habitación creada exitosamente',
            ]);
        } catch (\Exception $e) {
            // Deshacer la transacción en caso de error
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear la habitación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener información detallada sobre las habitaciones.
     *
     * Este método se encarga de recuperar información detallada sobre las habitaciones, incluyendo imágenes, características y habitaciones asociadas.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con detalles sobre las habitaciones o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
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
        r.has_decoracion AS hasDecoracion,
        r.has_desayuno AS hasDesayuno,
        r.incluye_desayuno AS incluyeDesayuno,
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
        ) AS caracteristicas,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", rs.id,"nombre", rs.nombre, "estado_id", rs.room_estado_id))
            FROM rooms rs
            WHERE rs.room_padre_id = r.id AND rs.deleted_at IS NULL
        ) AS rooms,
        (
            SELECT
            COUNT(*)
            FROM rooms rs
            WHERE rs.room_padre_id = r.id AND rs.deleted_at IS NULL
        ) AS countRooms
        FROM room_padre r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        JOIN room_estados re ON r.room_estado_id = re.id
        WHERE r.deleted_at IS NULL
        ORDER BY r.created_at DESC';

        $rooms = DB::select($query);

        foreach ($rooms as $room) {
            $room->habilitada = (bool) $room->habilitada;
            $room->hasDecoracion = (bool) $room->hasDecoracion;
            $room->hasDesayuno = (bool) $room->hasDesayuno;
            $room->incluyeDesayuno = (bool) $room->incluyeDesayuno;

            // Decodificar datos JSON
            $room->imgs = json_decode($room->imgs);
            $room->caracteristicas = json_decode($room->caracteristicas);
            $room->rooms = json_decode($room->rooms);

            // Obtener precios
            $room->precios = $this->getPrecios($room->id);
        }

        return response()->json($rooms, 200);
    }

    /**
     * Obtener información detallada sobre las habitaciones para clientes.
     *
     * Este método se encarga de recuperar información detallada sobre las habitaciones que cumplen con los requisitos para ser mostradas a los clientes, incluyendo imágenes y características.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con detalles sobre las habitaciones o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
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
        r.has_decoracion AS hasDecoracion,
        r.has_desayuno AS hasDesayuno,
        r.incluye_desayuno AS incluyeDesayuno,
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
        ) AS caracteristicas
        FROM room_padre r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        JOIN room_estados re ON r.room_estado_id = re.id
        WHERE r.deleted_at IS NULL
        AND EXISTS (
            SELECT 1
            FROM tarifas rt
            WHERE rt.room_id = r.id AND rt.deleted_at IS NULL
        )
        ORDER BY r.created_at DESC';

        $rooms = DB::select($query);

        foreach ($rooms as $room) {
            $room->habilitada = (bool) $room->habilitada;
            $room->hasDesayuno = (bool) $room->hasDesayuno;
            $room->hasDecoracion = (bool) $room->hasDecoracion;
            $room->incluyeDesayuno = (bool) $room->incluyeDesayuno;

            // Decodificar datos JSON
            $room->imgs = json_decode($room->imgs);
            $room->caracteristicas = json_decode($room->caracteristicas);

            // Obtener precios
            $room->precios = $this->getPrecios($room->id);
        }

        return response()->json($rooms, 200);
    }

    /**
     * Obtener información detallada sobre una habitación específica.
     *
     * Este método se encarga de recuperar información detallada sobre una habitación específica, incluyendo imágenes, características y cantidad de habitaciones disponibles.
     *
     * @param int $id Identificador de la habitación.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con detalles sobre la habitación o un mensaje de error en caso de que la habitación no exista, con detalles sobre el error.
     */
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
        r.has_decoracion AS hasDecoracion,
        r.has_desayuno AS hasDesayuno,
        r.incluye_desayuno AS incluyeDesayuno,
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
        ) AS caracteristicas,
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

        if (empty($rooms)) {
            return response()->json([
                'message' => 'Habitación inexistente',
            ], 404);
        }

        $room = $rooms[0];
        $room->habilitada = (bool) $room->habilitada;
        $room->hasDecoracion = (bool) $room->hasDecoracion;
        $room->hasDesayuno = (bool) $room->hasDesayuno;
        $room->incluyeDesayuno = (bool) $room->incluyeDesayuno;

        // Decodificar datos JSON
        $room->imgs = json_decode($room->imgs);
        $room->caracteristicas = json_decode($room->caracteristicas);

        // Obtener precios
        $room->precios = $this->getPrecios($room->id);

        return response()->json($room, 200);
    }

    /**
     * Guardar o actualizar precios para una habitación específica.
     *
     * Este método se encarga de guardar o actualizar los precios para una habitación específica en diferentes días de la semana y jornadas.
     *
     * @param \Illuminate\Http\Request $request Datos de la solicitud.
     * @param int $id Identificador de la habitación.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación o detalles sobre un error inesperado.
     */
    public function savePrecios(Request $request, int $id)
    {
        // Validar la solicitud
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
                            $fail('El formato de los días es incorrecto: { name: string, precio: integer }');
                            break;
                        }
                    }
                }
            ],
        ]);

        // Obtener los datos de los días y jornadas
        $weekdays = $request->input("weekdays");

        // Iniciar la transacción de la base de datos
        DB::beginTransaction();

        try {
            // Consulta SQL para insertar o actualizar los precios
            $query = 'INSERT INTO tarifas (
            room_id,
            nombre,
            precio,
            jornada_id,
            created_at)
            VALUES (?, ?, ?, ?, now())
            ON DUPLICATE KEY UPDATE
            precio = VALUES(precio),
            jornada_id = VALUES(jornada_id),
            updated_at = NOW()';

            // Iterar sobre los días y ejecutar la consulta
            foreach ($weekdays as $day) {
                DB::insert($query, [
                    $id,
                    $day['name'],
                    $day['precio'],
                    $day['jornada_id'],
                ]);
            }

            // Confirmar la transacción
            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'message' => 'Precios guardados exitosamente',
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Respuesta de error
            return response()->json([
                'message' => 'Error inesperado al guardar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener precios de habitación
     *
     * Este método se encarga de obtener los precios de una habitación específica.
     *
     * @param int $roomId Identificador de la habitación.
     * @return array Resultado de la consulta con los precios de la habitación.
     */
    public function getPrecios(int $roomId)
    {
        $query = 'SELECT
        rt.nombre AS name,
        rt.precio AS precio,
        tj.nombre AS jornada,
        rt.jornada_id AS jornada_id,
        rt.created_at AS created_at
        FROM tarifas rt
        LEFT JOIN tarifa_jornada tj ON tj.id = rt.jornada_id
        WHERE rt.room_id = ? AND rt.deleted_at IS NULL
        ORDER BY
        FIELD(rt.nombre, "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado")';

        $roomPrices = DB::select($query, [$roomId]);

        return $roomPrices;
    }

    /**
     * Obtener Jornadas de Tarifas
     *
     * Este método recupera las jornadas de tarifas disponibles.
     *
     * @return array Devuelve un array de objetos con las jornadas de tarifas.
     */
    public function getJornadas()
    {
        // Consulta SQL para obtener las jornadas de tarifas
        $query = 'SELECT id, nombre FROM tarifa_jornada';

        // Obtener resultados de la consulta
        $tarifas = DB::select($query);

        // Devolver el array de objetos con las jornadas de tarifas
        return $tarifas;
    }

    /**
     * Actualizar Habitación
     *
     * Este método se encarga de actualizar la información de una habitación y sus detalles asociados.
     *
     * @param \Illuminate\Http\Request $request Objeto Request con los datos de la actualización.
     * @param int $id Identificador de la habitación a actualizar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function update(Request $request, $id)
    {
        // Validación de los datos del formulario
        $request->validate([
            'user' => 'required',
            'nombre' => 'required',
            'descripcion' => 'required',
            'roomTipo' => 'required',
            'capacidad' => 'required',
            'estado' => 'required',
            'estadoAntiguo' => 'required',
            'decoracion' => 'required',
            'desayuno' => 'required',
            'incluyeDesayuno' => 'required',
        ]);

        // Consultas SQL para la actualización de la habitación y sus detalles asociados
        $query = 'UPDATE room_padre SET
        nombre = ?,
        descripcion = ?,
        room_tipo_id = ?,
        capacidad = ?,
        room_estado_id = ?,
        has_decoracion = ?,
        has_desayuno = ?,
        incluye_desayuno = ?,
        updated_at = now()
        WHERE id = ?';

        $queryRooms = 'UPDATE rooms SET
        descripcion = ?,
        room_tipo_id = ?,
        capacidad = ?,
        room_estado_id = ?,
        has_decoracion = ?,
        has_desayuno = ?,
        incluye_desayuno = ?,
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

        // Obtener datos de activación y desactivación de características
        $activar = $request->input('activar');
        $desactivar = $request->input('desactivar');

        DB::beginTransaction();

        try {
            // Actualizar la habitación principal
            DB::update($query, [
                $request->nombre,
                $request->descripcion,
                $request->roomTipo,
                $request->capacidad,
                $request->estado,
                $request->decoracion ? 1 : 0,
                $request->desayuno ? 1 : 0,
                $request->incluyeDesayuno ? 1 : 0,
                $id
            ]);

            // Actualizar las habitaciones asociadas
            DB::update($queryRooms, [
                $request->descripcion,
                $request->roomTipo,
                $request->capacidad,
                $request->estado,
                $request->decoracion ? 1 : 0,
                $request->desayuno ? 1 : 0,
                $request->incluyeDesayuno ? 1 : 0,
                $id
            ]);

            // Registrar el cambio en la bitácora de cambios
            RoomBitacoraCambioController::create($request->user, $id, $request->estado, $request->estadoAntiguo);

            // Activar características seleccionadas
            foreach ($activar as $caracteristicaId) {
                DB::insert($queryCaracteristicasCreate, [$id, $caracteristicaId]);
            }

            // Desactivar características seleccionadas
            foreach ($desactivar as $caracteristicaId) {
                DB::update($queryCaracteristicasUpdate, [$id, $caracteristicaId]);
            }

            // Confirmar la transacción
            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'message' => 'Actualizada exitosamente',
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Respuesta de error
            return response()->json([
                'message' => 'Error al actualizar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar Estado de Habitaciones
     *
     * Este método se encarga de actualizar el estado de una lista de habitaciones.
     *
     * @param \Illuminate\Http\Request $request Objeto Request con los datos de actualización.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function updateEstado(Request $request)
    {
        // Validación de los datos del formulario
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
                            $fail('El formato de las habitaciones es incorrecto. { id:integer, estado_id:integer }');
                            break;
                        }
                    }
                }
            ],
        ]);

        // Consulta SQL para la actualización del estado de las habitaciones
        $query = 'UPDATE rooms SET
        room_estado_id = ?,
        updated_at = now()
        WHERE id = ?';

        // Obtener datos de las habitaciones a actualizar
        $rooms = $request->input('rooms');

        DB::beginTransaction();

        try {
            // Actualizar el estado de cada habitación en la lista
            foreach ($rooms as $room) {
                DB::update($query, [
                    $room['estado_id'],
                    $room['id'],
                ]);
            }

            // Confirmar la transacción
            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'message' => 'Guardado Exitosamente',
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Respuesta de error
            return response()->json([
                'message' => 'Error Al Guardar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar Imágenes de Habitación
     *
     * Este método se encarga de actualizar las imágenes de una habitación.
     *
     * @param \Illuminate\Http\Request $request Objeto Request con los datos de actualización.
     * @param int $id Identificador de la habitación.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function updateImg(Request $request, $id)
    {
        // Consulta SQL para la inserción de nuevas imágenes
        $queryImagenes = 'INSERT INTO room_imgs (
        room_padre_id,
        url,
        created_at)
        VALUES (?, ?, NOW())';

        // Consulta SQL para el borrado de imágenes existentes
        $queryDelImgs = 'UPDATE room_imgs SET 
        deleted_at = now()
        WHERE id = ?';

        // Iniciar una transacción de base de datos
        DB::beginTransaction();

        try {
            // Procesar las imágenes enviadas para ser guardadas
            if ($request->hasFile('imgs')) {
                $images = $request->file('imgs');

                foreach ($images as $image) {
                    $ruta = $image->store('imgs', 'public');
                    // Insertar la nueva imagen en la base de datos
                    DB::insert($queryImagenes, [
                        $id,
                        $ruta,
                    ]);
                }
            }

            // Obtener las IDs de imágenes a eliminar
            $toDelete = $request->input('toDelete', []);

            // Eliminar las imágenes seleccionadas de la base de datos
            foreach ($toDelete as $imgID) {
                DB::update($queryDelImgs, [$imgID]);
            }

            // Confirmar la transacción
            DB::commit();

            // Eliminar los archivos físicos de las imágenes eliminadas
            $urls = $request->input('urls', []);

            foreach ($urls as $url) {
                $filePath = public_path('storage/' . $url);

                // Verificar si el archivo existe antes de intentar eliminarlo
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Respuesta exitosa
            return response()->json([
                'message' => 'Imágenes Guardadas',
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Respuesta de error
            return response()->json([
                'message' => 'Error Al Guardar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar Habitación
     *
     * Este método se encarga de marcar una habitación como eliminada en la base de datos.
     *
     * @param int $id Identificador de la habitación a eliminar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar la habitación como eliminada
        $query = 'UPDATE rooms SET 
        deleted_at = now()
        WHERE id = ?';

        // Ejecutar la consulta de actualización
        $room = DB::update($query, [$id]);

        // Verificar si la actualización fue exitosa
        if ($room) {
            // Respuesta exitosa
            return response()->json([
                'message' => 'Eliminada exitosamente',
            ]);
        } else {
            // Respuesta de error
            return response()->json([
                'message' => 'Error al actualizar',
            ], 500);
        }
    }
}
