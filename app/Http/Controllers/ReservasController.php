<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservasController extends Controller
{
    /**
     * Crear Reserva
     *
     * Este método se encarga de crear una nueva reserva en la base de datos.
     * La información de la reserva se recibe a través de una solicitud HTTP, se valida y se realiza la inserción de datos en la tabla correspondiente.
     *
     * @param Request $request Datos de entrada que incluyen información sobre la reserva.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function create(Request $request)
    {
        $request->validate([
            'dateIn' => 'required|string',
            'dateOut' => 'required|string',
            'room' => 'required|integer',
            'adultos' => 'required|integer',
            'niños' => 'required|integer',
            'precio' => 'required|integer',
            'abono' => 'required|integer',
            'useTarifasEspeciales' => 'required|boolean',
            'verificacion_pago' => 'required|integer',
            'huespedes' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $pago) {
                        $validate = validator($pago, [
                            'tipoDocumento' => 'required|integer',
                            'documento' => 'required|integer',
                            'nombre1' => 'required|string',
                            'apellido1' => 'required|string',
                            'telefono' => 'required|integer',
                        ]);

                        if ($validate->fails()) {
                            $fail('el formato de los huespedes es incorrecto');
                            break;
                        }
                    }
                }
            ],
        ]);

        $queryGetRoom = "SELECT r.id
        FROM rooms r
        LEFT JOIN reservas res ON res.room_id = r.id
            AND (
                (fecha_entrada BETWEEN ? AND ?)
                OR (fecha_salida BETWEEN ? AND ?)
                OR (fecha_entrada <= ? AND fecha_salida >= ?)
            )
        WHERE r.room_padre_id = ?
            AND r.deleted_at IS NULL
            AND res.id IS NULL";

        // Determinar la tabla y mensaje según la verificación de pago
        if ($request->verificacion_pago) {
            $table = "reservas";
            $message = "Reserva Hecha";
            $reserva = "reserva_id";
        } else {
            $table = "reservas_temporales";
            $message = "Se espera el pago de su reserva dentro de los siguientes 10 minutos o será eliminada su reserva";
            $reserva = "reserva_temporal_id";
        }

        // Consulta SQL para verificar disponibilidad de fechas
        $availabilityQuery = "SELECT r.id
        FROM $table r
        LEFT JOIN rooms rs ON rs.id = r.room_id
        WHERE rs.room_padre_id = ?
        AND r.deleted_at IS NULL
        AND (
            r.fecha_entrada BETWEEN ? AND ?
            OR r.fecha_salida BETWEEN ? AND ?
            OR (r.fecha_entrada <= ? AND r.fecha_salida >= ?)
        )";

        DB::beginTransaction();

        try {

            $huespedes = $request->input('huespedes');

            $rooms = DB::select($queryGetRoom, [
                $request->dateIn,
                $request->dateOut,
                $request->dateIn,
                $request->dateOut,
                $request->dateIn,
                $request->dateOut,
                $request->room,
            ]);

            if (count($rooms) == 0) {
                return response()->json([
                    'message' => 'No hay habitaciones disponibles',
                ], 400);
            }

            // Verificar disponibilidad de fechas
            $reservas = DB::select($availabilityQuery, [
                $request->room,
                $request->dateIn,
                $request->dateOut,
                $request->dateIn,
                $request->dateOut,
                $request->dateIn,
                $request->dateOut,
            ]);

            // Si hay reservas en proceso con esas fechas, retornar un error
            if (count($reservas) > 0) {
                return response()->json([
                    'message' => 'Hay una reserva en proceso con esos días, por favor, inténtelo de nuevo más tarde',
                ], 400);
            }

            // Consulta SQL para insertar la reserva
            $insertReserva = "INSERT INTO $table (
                fecha_entrada,
                fecha_salida,
                room_id,
                user_id,
                estado_id,
                desayuno_id,
                decoracion_id,
                adultos,
                niños,
                precio,
                abono,
                descuentos,
                cupon,
                tarifa_especial,
                verificacion_pago,
                created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            // Ejecutar la inserción de la reserva
            DB::insert($insertReserva, [
                $request->dateIn,
                $request->dateOut,
                $rooms[0]->id,
                isset($request->user) ? $request->user : 1, // Usuario web
                1, // Estado Pendiente
                isset($request->desayuno) ? $request->desayuno : null,
                isset($request->decoracion) ? $request->decoracion : null,
                $request->adultos,
                $request->niños,
                $request->precio,
                $request->abono,
                json_encode($request->descuentos),
                json_encode($request->cupon),
                $request->useTarifasEspeciales,
                $request->verificacion_pago,
            ]);

            // Obtener el ID de la reserva recién creada
            $reservaId = DB::getPdo()->lastInsertId();

            $insertClient = 'INSERT INTO clients (
            tipo_documento_id,
            documento,
            nombre1,
            nombre2,
            apellido1,
            apellido2,
            correo,
            telefono,
            pais_id,
            departamento_id,
            ciudad_id,
            created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

            $getClient = 'SELECT id
            FROM clients
            WHERE documento = ?';

            $insertHuespedReserva = "INSERT INTO reservas_huespedes (
            responsable,
            $reserva,
            cliente_id,
            motivo_id,
            pais_procedencia_id,
            departamento_procedencia_id,
            ciudad_procedencia_id,
            created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            if ($request->cupon) {
                $getCuponCode = 'SELECT id FROM tarifa_descuento_cupones_codigos WHERE codigo = ? AND usado = 0 AND activo = 1';

                $cuponCode = DB::selectOne($getCuponCode, [$request->cupon['codigo']]);

                if (!empty($cuponCode)) {
                    $saveCupon = "UPDATE tarifa_descuento_cupones_codigos SET
                    usado = 1,
                    updated_at = NOW()
                    WHERE id = ?";

                    $SaveCuponBitacora = "INSERT INTO tarifa_descuento_cupones_bitacora (
                    cedula,
                    cupon_id,
                    codigo_id,
                    created_at)
                    VALUES (?, ?, ?, NOW())";

                    DB::update($saveCupon, [
                        $cuponCode->id,
                    ]);

                    DB::insert($SaveCuponBitacora, [
                        $huespedes[0]['documento'],
                        $request->cupon['id'],
                        $cuponCode->id,
                    ]);
                } else {

                    DB::rollBack();

                    return response()->json([
                        'message' => 'El cupon ya a sido utilizado'
                    ], 500);
                }
            }

            foreach ($huespedes as $huesped) {

                $clientId = DB::selectOne($getClient, [
                    $huesped['documento']
                ]);

                if ($clientId) {
                    DB::insert($insertHuespedReserva, [
                        $huesped['responsable'],
                        $reservaId,
                        $clientId->id,
                        $huesped['motivo'],
                        $huesped['paisProcedencia'],
                        $huesped['departamentoProcedencia'],
                        $huesped['ciudadProcedencia'],
                    ]);
                } else {
                    DB::insert($insertClient, [
                        $request['tipoDocumento'],
                        $huesped['documento'],
                        $huesped['nombre1'],
                        $huesped['nombre2'],
                        $huesped['apellido1'],
                        $huesped['apellido2'],
                        isset($huesped['correo']) ? $huesped['correo'] : '',
                        $huesped['telefono'],
                        $huesped['paisResidencia'],
                        $huesped['departamentoResidencia'],
                        $huesped['ciudadResidencia'],
                    ]);

                    $clientId = DB::getPdo()->lastInsertId();

                    DB::insert($insertHuespedReserva, [
                        $huesped['responsable'],
                        $reservaId,
                        $clientId,
                        $huesped['motivo'],
                        $huesped['paisProcedencia'],
                        $huesped['departamentoProcedencia'],
                        $huesped['ciudadProcedencia'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => $message,
                'reserva' => $reservaId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al crear la reserva',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pagar Reserva
     *
     * Este método se encarga de registrar un pago para una reserva temporal en la base de datos.
     *
     * @param Request $request Datos de entrada que incluyen información sobre el pago.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function pagar(Request $request, int $id)
    {
        $request->validate([
            'reserva' => 'required|integer',
            'abono' => 'required|integer',
            'verificacion_pago' => 'required|integer',
        ]);

        // Consulta SQL para actualizar la reserva temporal con el pago
        $query = 'UPDATE reservas_temporales SET
        abono = ?,
        comprobante = ?,
        verificacion_pago = ?,
        updated_at = NOW()
        WHERE id = ? 
        AND deleted_at IS NULL';

        $rutaArchivo = null;

        try {
            // Verificar si se adjuntó un comprobante de pago
            if ($request->hasFile('comprobante')) {
                $file = $request->file('comprobante');
                $rutaArchivo = $file->store('comprobantes', 'public'); // Almacenar el archivo en la carpeta 'comprobantes' del almacenamiento público
            }

            // Ejecutar la actualización de la reserva temporal con el pago
            DB::update($query, [
                $request->abono,
                $rutaArchivo,
                $request->verificacion_pago,
                $request->reserva,
            ]);

            return response()->json([
                'message' => 'Pago registrado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al registrar el pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener Fechas de Reservas para una Habitación
     *
     * Este método se encarga de obtener las fechas de entrada y salida de las reservas asociadas a una habitación específica.
     *
     * @param Request $request Datos de entrada que incluyen la ID de la habitación.
     * @param int $id ID de la habitación.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON que contiene las fechas de entrada y salida de las reservas.
     */
    public function getDates(int $id)
    {
        // Consulta SQL para obtener las fechas de reservas asociadas a la habitación
        $query = 'SELECT r.fecha_entrada, r.fecha_salida
        FROM reservas r
        LEFT JOIN rooms rs ON rs.id = r.room_id
        WHERE rs.room_padre_id = ?
        AND r.deleted_at IS NULL
        AND YEAR(r.fecha_entrada) >= YEAR(CURDATE()) 
        AND YEAR(r.fecha_salida) >= YEAR(CURDATE())';

        try {
            // Obtener las fechas de las reservas
            $dates = DB::select($query, [
                $id,
            ]);

            // Retornar las fechas como respuesta JSON
            return response()->json($dates);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener las fechas de las reservas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener Todas las Reservas
     *
     * Este método se encarga de obtener todas las reservas de la base de datos con información adicional de las habitaciones y estados asociados.
     *
     * @param string $estado Estado de las reservas a obtener (por defecto, las no confirmadas).
     * @return \Illuminate\Http\JsonResponse Respuesta JSON que contiene la información detallada de las reservas.
     */
    public function read($estado = 'TODO')
    {
        $estados = [
            'TODO' => '',
            'Pendiente' => "AND r.estado_id = 1",
            'Confirmada' => "AND r.estado_id = 2",
            'No Confirmada' => "AND r.estado_id != 2 AND r.estado_id != 4",
            'Rechazada' => "AND r.estado_id = 3",
            'Cancelada' => "AND r.estado_id = 4",
        ];

        $getBitacoraCancelacion = '';

        if ($estado == 'Cancelada') {
            $getBitacoraCancelacion = "(
                SELECT
                JSON_ARRAYAGG(JSON_OBJECT(
                    'id', cb.id,
                    'tipoId', cb.tipo_id,
                    'tipo', ct.tipo,
                    'userId', cb.user_id,
                    'user', us.nombre,
                    'motivo', cb.nota_cancelacion,
                    'created_at', cb.created_at
                    ))
                FROM reservas_cancelacion_bitacora cb
                LEFT JOIN reservas_cancelacion_tipos ct ON ct.id = cb.tipo_id
                LEFT JOIN users us ON us.id = cb.user_id
                WHERE cb.deleted_at IS NULL AND cb.reserva_id = r.id
            ) AS bitacora,";
        }

        $getTemporales = 'ORDER BY r.created_at DESC';

        if ($estado == 'Pendiente') {
            $getTemporales = "UNION

            SELECT
                rt.id AS id,
                rt.fecha_entrada AS fechaEntrada,
                rt.fecha_salida AS fechaSalida,
                rt.room_id AS room,
                rt.user_id AS user,
                rt.estado_id AS estadoId,
                re2.estado AS estado,
                rt.desayuno_id AS desayunoId,
                rt.decoracion_id AS decoracionId,
                rt.adultos + rt.niños AS huespedes,
                rt.adultos AS adultos,
                rt.niños AS niños,
                rt.precio AS precio,
                rt.abono AS abono,
                rt.descuentos,
                rt.cupon,
                rt.tarifa_especial AS useTarifasEspeciales,
                rt.comprobante AS comprobante,
                rt.verificacion_pago AS verificacionPago,
                1 AS esTemporal,
                (
                    SELECT
                    JSON_ARRAYAGG(JSON_OBJECT(
                        'id', c2.id,
                        'fullname', CONCAT_WS(' ', c2.nombre1, c2.nombre2, c2.apellido1, c2.apellido2),
                        'documento', c2.documento,
                        'telefono', c2.telefono
                        ))
                     FROM clients c2
                     LEFT JOIN reservas_huespedes rh2 ON c2.id = rh2.cliente_id
                    WHERE c2.deleted_at IS NULL AND rh2.reserva_temporal_id = rt.id AND rh2.responsable = 1
                ) AS huesped,
                rt.created_at AS created_at
            FROM reservas_temporales rt
            JOIN reserva_estados re2 ON rt.estado_id = re2.id
            WHERE rt.deleted_at IS NULL
            AND rt.estado_id = 1
            ORDER BY created_at DESC
            ";
        }

        // Validar si el estado proporcionado es válido
        if (!in_array($estado, array_keys($estados))) {
            return response()->json(['message' => 'Estado no válido'], 400);
        }

        // Consulta SQL para obtener las reservas con información adicional
        $query = "SELECT
        r.id AS id,
        r.fecha_entrada AS fechaEntrada,
        r.fecha_salida AS fechaSalida,
        r.room_id AS roomId,
        r.user_id AS user,
        r.estado_id AS estadoId,
        re.estado AS estado,
        r.desayuno_id AS desayunoId,
        r.decoracion_id AS decoracionId,
        r.adultos + r.niños AS huespedes,
        r.adultos AS adultos,
        r.niños AS niños,
        r.precio AS precio,
        r.abono AS abono,
        r.descuentos,
        r.cupon,
        r.tarifa_especial AS useTarifasEspeciales,
        r.comprobante AS comprobante,
        r.verificacion_pago AS verificacionPago,
        0 AS esTemporal,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                'id', c.id,
                'fullname', CONCAT_WS(' ', c.nombre1, c.nombre2, c.apellido1, c.apellido2),
                'documento', c.documento,
                'telefono', c.telefono
                ))
            FROM clients c
            LEFT JOIN reservas_huespedes rh ON c.id = rh.cliente_id
            WHERE c.deleted_at IS NULL AND rh.reserva_id = r.id AND rh.responsable = 1
        ) AS huesped,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                'id', rp.id,
                'nombre', rp.nombre,
                'descripcion', rp.descripcion
                ))
            FROM room_padre rp
            WHERE rp.deleted_at IS NULL AND room.room_padre_id = rp.id
        ) AS room,
        $getBitacoraCancelacion
        r.created_at AS created_at
        FROM reservas r
        JOIN reserva_estados re ON r.estado_id = re.id
        JOIN rooms room ON r.room_id = room.id
        WHERE r.deleted_at IS NULL $estados[$estado]
        $getTemporales";

        try {
            // Obtener las reservas desde la base de datos
            $reservas = DB::select($query);

            // Iterar sobre las reservas para agregar información adicional
            foreach ($reservas as $reserva) {
                $reserva->verificacionPago = (bool) $reserva->verificacionPago;
                $reserva->useTarifasEspeciales = (bool) $reserva->useTarifasEspeciales;
                $reserva->esTemporal = (bool) $reserva->esTemporal;
                $reserva->huesped = json_decode($reserva->huesped);
                $reserva->descuentos = json_decode($reserva->descuentos);
                $reserva->cupon = json_decode($reserva->cupon);
                $reserva->room = json_decode($reserva->room);
                $reserva->room = $reserva->room[0];
                $reserva->huesped = $reserva->huesped[0];
                if ($estado == 'Cancelada') {
                    $reserva->bitacora = json_decode($reserva->bitacora);
                    $reserva->bitacora = $reserva->bitacora[0];
                }
            }

            // Retornar las reservas con la información adicional como respuesta JSON
            return response()->json($reservas, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener las reservas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Aprobar Reserva
     *
     * Este método se encarga de aprobar una reserva en la base de datos cambiando su estado.
     *
     * @param int $id ID de la reserva que se va a aprobar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'esTemporal' => 'required|boolean',
        ]);

        if ($request->esTemporal) {
            $table = "reservas_temporales";
        } else {
            $table = "reservas";
        }

        try {
            // Consulta SQL para actualizar el estado de la reserva a "Confirmada"
            $query = "UPDATE $table SET
            estado_id = ?,
            verificacion_pago = 1,
            updated_at = NOW()
            WHERE id = ?";

            // Ejecutar la actualización del estado de la reserva
            DB::update($query, [
                2,  // ID del estado "Confirmada"
                $id
            ]);

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Reserva Aprobada',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al aprobar la reserva',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rechazar Reserva
     *
     * Este método se encarga de rechazar una reserva en la base de datos cambiando su estado.
     *
     * @param int $id ID de la reserva que se va a rechazar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function reject(Request $request, int $id)
    {
        $request->validate([
            'esTemporal' => 'required|boolean',
        ]);

        if ($request->esTemporal) {
            $table = "reservas_temporales";
        } else {
            $table = "reservas";
        }

        try {
            // Consulta SQL para actualizar el estado de la reserva a "Rechazada"
            $query = "UPDATE $table SET
            estado_id = ?,
            updated_at = NOW()
            WHERE id = ?";

            // Ejecutar la actualización del estado de la reserva
            DB::update($query, [
                3,  // ID del estado "Rechazada"
                $id
            ]);

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Reserva Rechazada',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al rechazar la reserva',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancelar(Request $request, int $id)
    {
        $request->validate([
            'tipo' => 'required|integer',
            'user' => 'required|integer',
            'motivo' => 'required|string',
        ]);

        $queryInsert = 'INSERT INTO reservas_cancelacion_bitacora (
        tipo_id,
        user_id,
        nota_cancelacion,
        reserva_id,
        created_at)
        VALUES (?, ?, ?, ?, NOW())';

        $queryUpdate = 'UPDATE reservas SET
        estado_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        DB::beginTransaction();

        try {

            DB::insert($queryInsert, [
                $request->tipo,
                $request->user,
                $request->motivo,
                $id,
            ]);

            DB::update($queryUpdate, [
                4,  // ID del estado "Cancelada"
                $id
            ]);

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Reserva Cancelada',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al cancelar la reserva',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
