<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    /**
     * Crea una habitación principal y las habitaciones asociadas.
     *
     * Este método se encarga de crear una habitación principal y las habitaciones asociadas,
     * así como sus características y imágenes relacionadas.
     *
     * @param Request $request Datos de entrada que incluyen información sobre la habitación.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function create(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'tieneIva' => 'required|integer',
            'tipo' => 'required|integer',
            'capacidad' => 'required|integer',
            'estado' => 'required|integer',
            'cantidad' => 'required|integer',
            'cantidadOtas' => 'required|integer',
            'decoracion' => 'required|integer',
            'desayuno' => 'required|integer',
            'incluyeDesayuno' => 'required|integer',
        ]);

        // Consultas SQL para la inserción de datos
        $queryRoomPadre = 'INSERT INTO room_padre (
        nombre,
        descripcion,
        tiene_iva,
        impuesto_id,
        room_tipo_id,
        capacidad,
        room_estado_id,
        cantidad,
        cantidad_otas,
        tiene_decoracion,
        tiene_desayuno,
        incluye_desayuno,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

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
                $request->cantidadOtas,
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
     * Obtiene información detallada sobre las habitaciones.
     *
     * Este método se encarga de recuperar información detallada sobre las habitaciones,
     * incluyendo imágenes, características y habitaciones asociadas.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con detalles sobre las habitaciones o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function read()
    {
        // Consulta SQL para obtener información detallada sobre las habitaciones
        $query = 'SELECT
        rp.id AS id,
        rp.nombre AS nombre,
        rp.tiene_iva AS tieneIva,
        rp.impuesto_id AS impuestoId,
        rp.descripcion AS descripcion,
        rp.room_tipo_id AS tipoId,
        rt.tipo AS tipo,
        rp.room_estado_id AS estadoId,
        re.estado AS estado,
        rp.capacidad AS capacidad,
        rp.cantidad_otas AS cantidadOtas,
        rp.habilitada AS habilitada,
        rp.tiene_decoracion AS tieneDecoracion,
        rp.tiene_desayuno AS tieneDesayuno,
        rp.incluye_desayuno AS incluyeDesayuno,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", ri.id, "url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_padre_id = rp.id AND ri.deleted_at IS NULL
        ) AS imgs,
        (
            SELECT
            JSON_ARRAYAGG(rcr.caracteristica_id)
            FROM room_caracteristica_relacion rcr
            WHERE rcr.room_id = rp.id AND rcr.estado = 1 AND rcr.deleted_at IS NULL
        ) AS caracteristicas,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", rs.id,"nombre", rs.nombre, "estado_id", rs.room_estado_id))
            FROM rooms rs
            WHERE rs.room_padre_id = rp.id AND rs.deleted_at IS NULL
        ) AS rooms,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "name", rt.nombre,
                "jornada", tj.nombre,
                "jornada_id", rt.jornada_id,
                "impuestoId", rt.impuesto_id,
                "precio", rt.precio,
                "precioOtas", 
                CASE 
                    WHEN otas.es_porcentaje = 1
                        THEN rt.precio * (1 + otas.precio / 100)
                    ELSE rt.precio + otas.precio
                END
            ))
            FROM tarifas rt
            LEFT JOIN tarifa_jornada tj ON tj.id = rt.jornada_id
            LEFT JOIN tarifa_impuestos imp ON imp.id = rt.impuesto_id
            WHERE rt.room_id = rp.id
            ORDER BY
            FIELD(rt.nombre, "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Adicional", "Niños")
        ) AS precios,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "fecha", te.fecha,
                "precio", te.precio
            ))
            FROM tarifas_especiales te
            LEFT JOIN tarifa_impuestos imp ON imp.id = te.impuesto_id
            WHERE te.room_id = rp.id AND te.deleted_at IS NULL
            ORDER BY te.created_at DESC
        ) AS tarifasEspeciales,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "precio", ota.precio,
                "tipo",
                CASE 
                    WHEN ota.es_porcentaje = 1
                        THEN "Porcentaje"
                    ELSE "Precio"
                END
            ))
            FROM tarifas_otas ota
            WHERE ota.id = otas.id
        ) AS tarifaOta,
        (
            SELECT
            COUNT(*)
            FROM rooms rs
            WHERE rs.room_padre_id = rp.id AND rs.deleted_at IS NULL
        ) AS countRooms
        FROM room_padre rp
        JOIN room_tipos rt ON rp.room_tipo_id = rt.id
        JOIN room_estados re ON rp.room_estado_id = re.id
        LEFT JOIN tarifas_otas otas ON otas.room_id = rp.id
        WHERE rp.deleted_at IS NULL
        AND rp.habilitada = 1
        ORDER BY rp.created_at DESC';

        // Ejecutar la consulta SQL
        $rooms = DB::select($query);

        // Decodificar datos JSON y ajustar valores booleanos
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
            $room->tarifaOta = json_decode($room->tarifaOta);

            if ($room->tarifaOta != null) {
                $room->tarifaOta = $room->tarifaOta[0];
            }
        }

        // Devolver la respuesta JSON con detalles sobre las habitaciones
        return response()->json($rooms, 200);
    }

    /**
     * Obtiene información detallada sobre las habitaciones para clientes.
     *
     * Este método se encarga de recuperar información detallada sobre las habitaciones que cumplen con los requisitos para ser mostradas a los clientes, incluyendo imágenes y características.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con detalles sobre las habitaciones o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function readClient()
    {
        // Consulta SQL para obtener información detallada sobre las habitaciones para clientes
        $query = 'SELECT
        rp.id AS id,
        rp.nombre AS nombre,
        rp.descripcion AS descripcion,
        rp.capacidad AS capacidad,
        rp.cantidad_otas AS cantidadOtas,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_padre_id = rp.id AND ri.deleted_at IS NULL
        ) AS imgs,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "name", rt.nombre,
                "jornada", tj.nombre,
                "precio", rt.precio,
                "previoFestivo", rt.precio_previo_festivo,
                "precioConIva", 
                CASE 
                    WHEN rt.impuesto_id IS NOT NULL
                        THEN rt.precio * (1 + imp.tasa / 100)
                    ELSE rt.precio
                END
            ))
            FROM tarifas rt
            LEFT JOIN tarifa_jornada tj ON tj.id = rt.jornada_id
            LEFT JOIN tarifa_impuestos imp ON imp.id = rt.impuesto_id
            WHERE rt.room_id = rp.id
            ORDER BY
            FIELD(rt.nombre, "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Adicional", "Niños")
        ) AS precios,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "fechaEntrada", res.fecha_entrada,
                "fechaSalida", res.fecha_salida
            ))
            FROM reservas res
            JOIN rooms rs ON rs.id = res.room_id
            WHERE res.fecha_salida >= CURRENT_DATE
            AND rs.room_padre_id = rp.id
        ) AS reservas,
        (
            SELECT
            COUNT(*)
            FROM rooms rs
            WHERE rs.room_padre_id = rp.id AND rs.habilitada = 1 AND rs.deleted_at IS NULL
        ) AS cantidad,
        (
            SELECT
            JSON_ARRAYAGG(rcr.caracteristica_id)
            FROM room_caracteristica_relacion rcr
            WHERE rcr.room_id = rp.id AND rcr.estado = 1 AND rcr.deleted_at IS NULL
        ) AS caracteristicas
        FROM room_padre rp
        WHERE rp.deleted_at IS NULL
        AND rp.habilitada = 1
        AND (
            SELECT COUNT(*)
            FROM tarifas rt
            WHERE rt.room_id = rp.id AND rt.deleted_at IS NULL
        ) >= 7
        ORDER BY rp.created_at DESC';

        // Ejecutar la consulta SQL
        $rooms = DB::select($query);

        // Decodificar datos JSON y ajustar valores booleanos
        foreach ($rooms as $room) {

            // Decodificar datos JSON
            $room->imgs = json_decode($room->imgs);
            $room->caracteristicas = json_decode($room->caracteristicas);
            $room->precios = json_decode($room->precios);
            $room->reservas = json_decode($room->reservas);
        }

        // Devolver la respuesta JSON con detalles sobre las habitaciones para clientes
        return response()->json($rooms, 200);
    }

    /**
     * Obtiene información detallada sobre una habitación específica.
     *
     * Este método se encarga de recuperar información detallada sobre una habitación específica, incluyendo imágenes, características y cantidad de habitaciones disponibles.
     *
     * @param int $id Identificador de la habitación.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con detalles sobre la habitación o un mensaje de error en caso de que la habitación no exista, con detalles sobre el error.
     */
    public function find($id)
    {
        // Consulta SQL para obtener información detallada sobre una habitación específica
        $query = 'SELECT
        rp.id AS id,
        rp.nombre AS nombre,
        rp.descripcion AS descripcion,
        rt.tipo AS tipo,
        re.estado AS estado,
        rp.capacidad AS capacidad,
        rp.tiene_decoracion AS tieneDecoracion,
        rp.tiene_desayuno AS tieneDesayuno,
        rp.incluye_desayuno AS incluyeDesayuno,
        im.tasa AS iva,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", ri.id, "url", ri.url))
            FROM room_imgs ri 
            WHERE ri.room_padre_id = rp.id AND ri.deleted_at IS NULL
        ) AS imgs,
        (
            SELECT
            JSON_ARRAYAGG(rcr.caracteristica_id)
            FROM room_caracteristica_relacion rcr
            WHERE rcr.room_id = rp.id AND rcr.estado = 1 AND rcr.deleted_at IS NULL
        ) AS caracteristicas,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "name", rt.nombre,
                "jornada", tj.nombre,
                "precio", rt.precio,
                "previoFestivo", rt.precio_previo_festivo,
                "precioConIva", 
                CASE 
                    WHEN rt.impuesto_id IS NOT NULL
                        THEN rt.precio * (1 + imp.tasa / 100)
                    ELSE rt.precio
                END
            ))
            FROM tarifas rt
            LEFT JOIN tarifa_jornada tj ON tj.id = rt.jornada_id
            LEFT JOIN tarifa_impuestos imp ON imp.id = rt.impuesto_id
            WHERE rt.room_id = rp.id
            ORDER BY
            FIELD(rt.nombre, "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Adicional", "Niños")
        ) AS precios,
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
            JSON_ARRAYAGG(JSON_OBJECT(
                "fecha", te.fecha,
                "precio", te.precio
            ))
            FROM tarifas_especiales te
            WHERE te.room_id = rp.id AND te.deleted_at IS NULL
            ORDER BY te.created_at DESC
        ) AS tarifasEspeciales,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "fechaEntrada", res.fecha_entrada,
                "fechaSalida", res.fecha_salida
            ))
            FROM reservas res
            JOIN rooms rs ON rs.id = res.room_id
            WHERE res.fecha_salida >= CURRENT_DATE
            AND rs.room_padre_id = rp.id
        ) AS reservas,
        (
            SELECT
            COUNT(*)
            FROM rooms rs
            WHERE rs.room_padre_id = rp.id AND rs.habilitada = 1 AND rs.deleted_at IS NULL
        ) AS cantidad
        FROM room_padre rp
        JOIN room_tipos rt ON rp.room_tipo_id = rt.id
        JOIN room_estados re ON rp.room_estado_id = re.id
        JOIN configuracions config ON config.id = 1
        LEFT JOIN tarifa_impuestos im ON im.id = rp.impuesto_id
        WHERE rp.id = ? && rp.deleted_at IS NULL
        AND rp.habilitada = 1';

        $room = DB::selectOne($query, [$id]);

        if ($room) {
            // Ajustar valores booleanos
            $room->tieneDecoracion = (bool) $room->tieneDecoracion;
            $room->tieneDesayuno = (bool) $room->tieneDesayuno;
            $room->incluyeDesayuno = (bool) $room->incluyeDesayuno;

            // Decodificar datos JSON
            $room->imgs = json_decode($room->imgs);
            $room->caracteristicas = json_decode($room->caracteristicas);
            $room->precios = json_decode($room->precios);
            $room->tarifasEspeciales = json_decode($room->tarifasEspeciales);
            $room->tarifasGenerales = json_decode($room->tarifasGenerales);
            $room->reservas = json_decode($room->reservas);

            // Devolver la respuesta JSON con detalles sobre la habitación
            return response()->json($room, 200);
        } else {
            // Devolver un mensaje de error si la habitación no existe
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
            'cantidadOtas' => 'required|integer',
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
        cantidad_otas = ?,
        room_estado_id = ?,
        tiene_decoracion = ?,
        tiene_desayuno = ?,
        incluye_desayuno = ?,
        updated_at = NOW()
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
                $request->cantidadOtas,
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
     * Elimina una habitación y todas sus relaciones de la base de datos.
     *
     * Este método marca una habitación y todas sus relaciones (habitaciones secundarias, tarifas y imágenes) como eliminadas en la base de datos.
     *
     * @param int $id Identificador de la habitación a eliminar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function delete($id)
    {
        // Consultas SQL para marcar la habitación y sus relaciones como eliminadas
        $queryRoomPadre = 'UPDATE room_padre SET
        deleted_at = NOW()
        WHERE id = ?';

        $queryRooms = 'UPDATE rooms SET 
        deleted_at = NOW()
        WHERE room_padre_id = ?';

        $queryTarifas = 'UPDATE tarifas SET 
        deleted_at = NOW()
        WHERE room_padre_id = ?';

        $queryGetImgs = 'SELECT id, url
        FROM room_imgs
        WHERE room_padre_id = ?';

        $queryDelImgs = 'UPDATE room_imgs SET 
        deleted_at = NOW()
        WHERE room_padre_id = ?';

        DB::beginTransaction();

        try {
            // Marcar la habitación principal como eliminada
            DB::update($queryRoomPadre, [$id]);

            // Marcar las habitaciones secundarias como eliminadas
            DB::update($queryRooms, [$id]);

            // Marcar las tarifas asociadas como eliminadas
            DB::update($queryTarifas, [$id]);

            // Obtener las imágenes asociadas a la habitación
            $imgs = DB::select($queryGetImgs, [$id]);

            // Eliminar las imágenes físicas y marcarlas como eliminadas en la base de datos
            foreach ($imgs as $img) {
                $filePath = public_path('storage/' . $img->url);

                // Verificar si el archivo existe antes de intentar eliminarlo
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Marcar las imágenes como eliminadas en la base de datos
            DB::update($queryDelImgs, [$id]);

            // Confirmar la transacción
            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'message' => 'Habitación eliminadas exitosamente',
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Respuesta de error
            return response()->json([
                'message' => 'Error al eliminar la habitación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina una habitación secundaria.
     *
     * Este método marca la habitación secundaria como eliminada en la base de datos.
     *
     * @param int $id Identificador de la habitación secundaria.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function deleteHija($id)
    {
        // Consulta SQL para marcar la habitación como eliminada
        $query = 'UPDATE rooms SET 
        deleted_at = NOW()
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
                // Si no se pudo eliminar, devolver un mensaje de error
                return response()->json([
                    'message' => 'Error al eliminar la habitación. La habitación no existe o ya ha sido eliminada.',
                ], 404);
            }
        } catch (\Exception $e) {
            // Capturar cualquier excepción y devolver un mensaje de error
            return response()->json([
                'message' => 'Error al eliminar la habitación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
