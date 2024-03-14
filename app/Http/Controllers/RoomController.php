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
            'tieneIva' => 'required|integer',
            'tipo' => 'required|integer',
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
        tiene_iva,
        impuesto_id,
        room_tipo_id,
        capacidad,
        room_estado_id,
        cantidad,
        tiene_decoracion,
        tiene_desayuno,
        incluye_desayuno,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        $queryRooms = 'INSERT INTO rooms (
        room_padre_id,
        nombre,
        room_estado_id,
        created_at)
        VALUES (?, ?, ?, NOW())';

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
                $request->tieneIva,
                $request->tieneIva ? $request->impuesto : null,
                $request->tipo,
                $request->capacidad,
                $request->estado,
                $request->cantidad,
                $request->decoracion,
                $request->desayuno,
                $request->incluyeDesayuno,
            ]);

            // Obtener el ID de la habitación principal
            $roomId = DB::getPdo()->lastInsertId();

            // Insertar habitaciones asociadas
            for ($i = 0; $i < $request->cantidad; $i++) {
                DB::insert($queryRooms, [
                    $roomId,
                    'Habitacion - ' . (1 + $i),
                    $request->estado,
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
        r.tiene_iva AS tieneIva,
        r.impuesto_id AS impuestoId,
        r.descripcion AS descripcion,
        r.room_tipo_id AS tipoId,
        rt.tipo AS tipo,
        r.room_estado_id AS estadoId,
        re.estado AS estado,
        r.capacidad AS capacidad,
        r.habilitada AS habilitada,
        r.tiene_decoracion AS tieneDecoracion,
        r.tiene_desayuno AS tieneDesayuno,
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
            JSON_ARRAYAGG(JSON_OBJECT(
                "name", rt.nombre,
                "jornada", tj.nombre,
                "jornada_id", rt.jornada_id,
                "impuestoId", rt.impuesto_id,
                "precio", rt.precio,
                "precio_con_iva", 
                CASE 
                    WHEN rt.impuesto_id IS NOT NULL
                        THEN rt.precio * (1 + imp.tasa / 100)
                        ELSE rt.precio
                    END
            ))
            FROM tarifas rt
            LEFT JOIN tarifa_jornada tj ON tj.id = rt.jornada_id
            LEFT JOIN tarifa_impuestos imp ON imp.id = rt.impuesto_id
            WHERE rt.room_id = r.id
            ORDER BY
            FIELD(rt.nombre, "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Adicional", "Niños")
        ) AS precios,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "fechaInicio", te.fecha_inicio,
                "fechaFin", te.fecha_fin,
                "precio", te.precio,
                "precio_con_iva", 
                CASE 
                    WHEN te.impuesto_id IS NOT NULL
                        THEN te.precio * (1 + imp.tasa / 100)
                        ELSE te.precio
                    END
            ))
            FROM tarifas_especiales te
            LEFT JOIN tarifa_impuestos imp ON imp.id = te.impuesto_id
            WHERE te.room_id = r.id AND te.deleted_at IS NULL
            ORDER BY te.created_at DESC
        ) AS tarifasEspeciales,
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
            $room->tieneDecoracion = (bool) $room->tieneDecoracion;
            $room->tieneDesayuno = (bool) $room->tieneDesayuno;
            $room->tieneIva = (bool) $room->tieneIva;
            $room->incluyeDesayuno = (bool) $room->incluyeDesayuno;

            // Decodificar datos JSON
            $room->imgs = json_decode($room->imgs);
            $room->caracteristicas = json_decode($room->caracteristicas);
            $room->rooms = json_decode($room->rooms);
            $room->precios = json_decode($room->precios);
            $room->tarifasEspeciales = json_decode($room->tarifasEspeciales);
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
        r.tiene_decoracion AS tieneDecoracion,
        r.tiene_desayuno AS tieneDesayuno,
        r.incluye_desayuno AS incluyeDesayuno,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", ri.id, "url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_padre_id = r.id AND ri.deleted_at IS NULL
        ) AS imgs,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "name", rt.nombre,
                "jornada", tj.nombre,
                "jornada_id", rt.jornada_id,
                "impuestoId", rt.impuesto_id,
                "precio", rt.precio,
                "precio_con_iva", 
                CASE 
                    WHEN rt.impuesto_id IS NOT NULL
                        THEN rt.precio * (1 + imp.tasa / 100)
                        ELSE rt.precio
                    END
            ))
            FROM tarifas rt
            LEFT JOIN tarifa_jornada tj ON tj.id = rt.jornada_id
            LEFT JOIN tarifa_impuestos imp ON imp.id = rt.impuesto_id
            WHERE rt.room_id = r.id
            ORDER BY
            FIELD(rt.nombre, "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Adicional", "Niños")
        ) AS precios,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "fechaInicio", te.fecha_inicio,
                "fechaFin", te.fecha_fin,
                "precio", te.precio,
                "precio_con_iva", 
                CASE 
                    WHEN te.impuesto_id IS NOT NULL
                        THEN te.precio * (1 + imp.tasa / 100)
                        ELSE te.precio
                    END
            ))
            FROM tarifas_especiales te
            LEFT JOIN tarifa_impuestos imp ON imp.id = te.impuesto_id
            WHERE te.room_id = r.id AND te.deleted_at IS NULL
            ORDER BY te.created_at DESC
        ) AS tarifasEspeciales,
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
            $room->tieneDesayuno = (bool) $room->tieneDesayuno;
            $room->tieneDecoracion = (bool) $room->tieneDecoracion;
            $room->incluyeDesayuno = (bool) $room->incluyeDesayuno;

            // Decodificar datos JSON
            $room->imgs = json_decode($room->imgs);
            $room->caracteristicas = json_decode($room->caracteristicas);
            $room->precios = json_decode($room->precios);
            $room->tarifasEspeciales = json_decode($room->tarifasEspeciales);
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
        r.tiene_iva AS tieneIva,
        r.tiene_decoracion AS tieneDecoracion,
        r.tiene_desayuno AS tieneDesayuno,
        r.incluye_desayuno AS incluyeDesayuno,
        im.tasa AS iva,
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
            JSON_ARRAYAGG(JSON_OBJECT(
                "name", rt.nombre,
                "jornada", tj.nombre,
                "jornada_id", rt.jornada_id,
                "impuestoId", rt.impuesto_id,
                "precio", rt.precio,
                "previoFestivo", rt.precio_previo_festivo,
                "precioConIva", 
                CASE 
                    WHEN rt.impuesto_id IS NOT NULL
                        THEN rt.precio * (1 + imp.tasa / 100)
                    ELSE rt.precio
                END,
                "previoFestivoConIva",
                CASE 
                    WHEN rt.impuesto_id IS NOT NULL
                        THEN ROUND(rt.precio_previo_festivo * (1 + imp.tasa/100))
                    ELSE ROUND(rt.precio_previo_festivo)
                END
            ))
            FROM tarifas rt
            LEFT JOIN tarifa_jornada tj ON tj.id = rt.jornada_id
            LEFT JOIN tarifa_impuestos imp ON imp.id = rt.impuesto_id
            WHERE rt.room_id = r.id
            ORDER BY
            FIELD(rt.nombre, "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Adicional", "Niños")
        ) AS precios,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "fechaInicio", te.fecha_inicio,
                "fechaFin", te.fecha_fin,
                "precio", te.precio,
                "precioConIva", 
                CASE 
                    WHEN te.impuesto_id IS NOT NULL
                        THEN te.precio * (1 + imp.tasa / 100)
                        ELSE te.precio
                    END
            ))
            FROM tarifas_especiales te
            LEFT JOIN tarifa_impuestos imp ON imp.id = te.impuesto_id
            WHERE te.room_id = r.id AND te.deleted_at IS NULL
            ORDER BY te.created_at DESC
        ) AS tarifasEspeciales,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "nombre", tg.nombre,
                "precio", tg.precio,
                "precioConIva", 
                CASE 
                    WHEN tg.impuesto_id IS NOT NULL
                        THEN tg.precio * (1 + imp.tasa / 100)
                        ELSE tg.precio
                    END
            ))
            FROM tarifas_generales tg
            LEFT JOIN tarifa_impuestos imp ON imp.id = tg.impuesto_id
            ORDER BY tg.created_at DESC
        ) AS tarifasGenerales,
        (
            SELECT
            COUNT(*)
            FROM rooms rs
            WHERE rs.room_padre_id = r.id AND rs.habilitada = 1 AND rs.deleted_at IS NULL
        ) AS rooms
        FROM room_padre r
        JOIN room_tipos rt ON r.room_tipo_id = rt.id
        JOIN room_estados re ON r.room_estado_id = re.id
        JOIN configuracions config ON config.id = 1
        LEFT JOIN tarifa_impuestos im ON im.id = r.impuesto_id
        WHERE r.id = ? && r.deleted_at IS NULL';

        $room = DB::selectOne($query, [$id]);

        if ($room) {

            $room->habilitada = (bool) $room->habilitada;
            $room->tieneDecoracion = (bool) $room->tieneDecoracion;
            $room->tieneDesayuno = (bool) $room->tieneDesayuno;
            $room->incluyeDesayuno = (bool) $room->incluyeDesayuno;
            $room->tieneIva = (bool) $room->tieneIva;

            // Decodificar datos JSON
            $room->imgs = json_decode($room->imgs);
            $room->caracteristicas = json_decode($room->caracteristicas);
            $room->precios = json_decode($room->precios);
            $room->tarifasEspeciales = json_decode($room->tarifasEspeciales);
            $room->tarifasGenerales = json_decode($room->tarifasGenerales);

            return response()->json($room, 200);
        } else {
            return response()->json([
                'message' => 'Habitación no encontrada',
            ], 404);
        }
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
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'tieneIva' => 'required|integer',
            'tipo' => 'required|integer',
            'capacidad' => 'required|integer',
            'estado' => 'required|integer',
            'estadoAntiguo' => 'required|integer',
            'cantidad' => 'required|integer',
            'decoracion' => 'required|integer',
            'desayuno' => 'required|integer',
            'incluyeDesayuno' => 'required|integer',
        ]);

        // Consultas SQL para la actualización de la habitación y sus detalles asociados
        $query = 'UPDATE room_padre SET
        nombre = ?,
        descripcion = ?,
        tiene_iva = ?,
        impuesto_id = ?,
        room_tipo_id = ?,
        capacidad = ?,
        room_estado_id = ?,
        tiene_decoracion = ?,
        tiene_desayuno = ?,
        incluye_desayuno = ?,
        updated_at = now()
        WHERE id = ?';

        $queryRooms = 'INSERT INTO rooms (
        room_padre_id,
        nombre,
        room_estado_id,
        created_at)
        VALUES (?, ?, ?, NOW())';

        $queryGetCountRooms = 'SELECT
        COUNT(*) AS cantidad
        FROM rooms rs
        WHERE rs.room_padre_id = ? AND rs.habilitada = 1 AND rs.deleted_at IS NULL';

        $queryUpdateRooms = 'UPDATE rooms SET
        room_estado_id = ?,
        updated_at = now()
        WHERE room_padre_id = ? AND deleted_at IS NULL';

        $queryUpdateTarifas = 'UPDATE tarifas SET
        impuesto_id = ?,
        updated_at = now()
        WHERE room_id = ? AND nombre NOT IN ("Adicional", "Niños") AND deleted_at IS NULL';

        $queryUpdateTarifasEspeciales = 'UPDATE tarifas_especiales SET
        impuesto_id = ?,
        updated_at = now()
        WHERE room_id = ? AND deleted_at IS NULL';

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
                $request->tieneIva,
                $request->tieneIva ? $request->impuesto : null,
                $request->tipo,
                $request->capacidad,
                $request->estado,
                $request->decoracion ? 1 : 0,
                $request->desayuno ? 1 : 0,
                $request->incluyeDesayuno ? 1 : 0,
                $id
            ]);

            DB::update($queryUpdateTarifas, [
                $request->tieneIva ? $request->impuesto : null,
                $id
            ]);

            DB::update($queryUpdateTarifasEspeciales, [
                $request->tieneIva ? $request->impuesto : null,
                $id
            ]);

            $result = DB::selectOne($queryGetCountRooms, [$id]);

            $newCountForAdd = $request->cantidad - $result->cantidad;

            // Insertar habitaciones asociadas
            for ($i = 0; $i < $newCountForAdd; $i++) {
                DB::insert($queryRooms, [
                    $id,
                    'Habitacion - ' . ($result->cantidad + 1 + $i),
                    $request->estado,
                ]);
            }

            // Actualizar las habitaciones asociadas
            DB::update($queryUpdateRooms, [
                $request->estado,
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
     * Actualizar Habitaciones
     *
     * Este método se encarga de actualizar el estado de una lista de habitaciones.
     *
     * @param \Illuminate\Http\Request $request Objeto Request con los datos de actualización.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function updateRooms(Request $request)
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
                            'nombre' => 'required|string',
                            'estado_id' => 'required|integer',
                        ]);

                        if ($validate->fails()) {
                            $fail('El formato de las habitaciones es incorrecto. { id:integer, nombre:string estado_id:integer }');
                            break;
                        }
                    }
                }
            ],
        ]);

        // Consulta SQL para la actualización de las habitaciones
        $query = 'UPDATE rooms SET
        nombre = ?,
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
                    $room['nombre'],
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
        $query = 'UPDATE room_padre SET
        deleted_at = now()
        WHERE id = ?';

        $queryRooms = 'UPDATE rooms SET 
        deleted_at = now()
        WHERE room_padre_id = ?';

        $queryTarifas = 'UPDATE tarifas SET 
        deleted_at = now()
        WHERE room_padre_id = ?';

        $queryGetImgs = 'SELECT id, url
        FROM room_imgs
        WHERE room_padre_id = ?';

        $queryDelImgs = 'UPDATE room_imgs SET 
        deleted_at = now()
        WHERE room_padre_id = ?';

        DB::beginTransaction();

        try {
            // Ejecutar la consulta de actualización
            DB::update($query, [$id]);
            DB::update($queryRooms, [$id]);
            DB::update($queryTarifas, [$id]);
            DB::update($queryDelImgs, [$id]);
            $imgs = DB::select($queryGetImgs, [$id]);

            foreach ($imgs as $img) {
                $filePath = public_path('storage/' . $img->url);

                // Verificar si el archivo existe antes de intentar eliminarlo
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'message' => 'Eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Respuesta de error
            return response()->json([
                'message' => 'Error al eliminar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteHija($id)
    {
        // Consulta SQL para marcar la habitación como eliminada
        $query = 'UPDATE rooms SET 
        deleted_at = now()
        WHERE id = ?';

        try {
            // Ejecutar la consulta de actualización
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Habitación eliminada exitosamente',
                    'id' => $id,
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar la habitación',
                ], 500);
            }
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Respuesta de error
            return response()->json([
                'message' => 'Error al eliminar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
