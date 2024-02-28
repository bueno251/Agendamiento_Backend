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
            'cedula' => 'required|string',
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'correo' => 'required|string',
            'telefono' => 'required|string',
            'dateIn' => 'required|string',
            'dateOut' => 'required|string',
            'room' => 'required|integer',
            'adultos' => 'required|integer',
            'niños' => 'required|integer',
            'precio' => 'required|integer',
            'cantidad_rooms' => 'required|integer',
            'verificacion_pago' => 'required|integer',
        ]);

        // Determinar la tabla y mensaje según la verificación de pago
        if ($request->verificacion_pago) {
            $table = "reservas";
            $message = "Reserva Hecha";
        } else {
            $table = "reservas_temporales";
            $message = "Se espera el pago de su reserva dentro de los siguientes 10 minutos o será eliminada su reserva";
        }

        // Consulta SQL para verificar disponibilidad de fechas
        $availabilityQuery = "SELECT id
        FROM $table
        WHERE room_id = ?
        AND deleted_at IS NULL
        AND (
            fecha_entrada BETWEEN ? AND ?
            OR fecha_salida BETWEEN ? AND ?
            OR (fecha_entrada <= ? AND fecha_salida >= ?)
        )";

        try {
            // Verificar disponibilidad de fechas
            $temporales = DB::select($availabilityQuery, [
                $request->room,
                $request->dateIn,
                $request->dateOut,
                $request->dateIn,
                $request->dateOut,
                $request->dateIn,
                $request->dateOut,
            ]);

            // Si hay reservas en proceso con esas fechas, retornar un error
            if (count($temporales) > 0) {
                return response()->json([
                    'message' => 'Hay una reserva en proceso con esos días, por favor, inténtelo de nuevo más tarde',
                ], 400);
            }

            // Consulta SQL para insertar la reserva
            $insertQuery = "INSERT INTO $table (
                fecha_entrada,
                fecha_salida,
                cedula,
                nombre,
                apellido,
                correo,
                telefono,
                room_id,
                cliente_id,
                user_id,
                estado_id,
                desayuno_id,
                decoracion_id,
                huespedes,
                adultos,
                niños,
                precio,
                verificacion_pago,
                created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";


            // Ejecutar la inserción de la reserva
            $reservaT = DB::insert($insertQuery, [
                $request->dateIn,
                $request->dateOut,
                $request->cedula,
                $request->nombre,
                $request->apellido,
                $request->correo,
                $request->telefono,
                $request->room,
                isset($request->cliente) ? $request->cliente : null,
                isset($request->user) ? $request->user : 1, // Usuario web
                1, // Estado Pendiente
                isset($request->desayuno) ? $request->desayuno : null,
                isset($request->decoracion) ? $request->decoracion : null,
                $request->adultos + $request->niños,
                $request->adultos,
                $request->niños,
                $request->precio,
                $request->verificacion_pago,
            ]);

            // Obtener el ID de la reserva recién creada
            $id = DB::getPdo()->lastInsertId();

            // Verificar si la inserción fue exitosa
            if ($reservaT) {
                return response()->json([
                    'message' => $message,
                    'reserva' => $id,
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al crear la reserva',
                ], 500);
            }
        } catch (\Exception $e) {
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
        $query = 'SELECT fecha_entrada, fecha_salida
        FROM reservas
        WHERE room_id = ?
        AND deleted_at IS NULL
        AND YEAR(fecha_entrada) >= YEAR(CURDATE()) 
        AND YEAR(fecha_salida) >= YEAR(CURDATE())';

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
                FROM cancelacion_bitacora cb
                LEFT JOIN cancelacion_tipos ct ON ct.id = cb.tipo_id
                LEFT JOIN users us ON us.id = cb.user_id
                WHERE cb.deleted_at IS NULL AND cb.reserva_id = r.id
            ) AS bitacora,";
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
        r.room_id AS room,
        r.cliente_id AS cliente,
        r.user_id AS user,
        r.estado_id AS estadoId,
        re.estado AS estado,
        r.desayuno_id AS desayunoId,
        r.decoracion_id AS decoracionId,
        r.cedula AS cedula,
        r.telefono AS telefono,
        r.nombre AS nombre,
        r.apellido AS apellido,
        CONCAT_WS(' ', r.nombre, r.apellido) AS fullname,
        r.correo AS correo,
        r.huespedes AS huespedes,
        r.adultos AS adultos,
        r.niños AS niños,
        r.precio AS precio,
        r.abono AS abono,
        r.comprobante AS comprobante,
        r.verificacion_pago AS verificacionPago,
        $getBitacoraCancelacion
        r.created_at AS created_at
        FROM reservas r
        JOIN reserva_estados re ON r.estado_id = re.id
        WHERE r.deleted_at IS NULL $estados[$estado]
        ORDER BY r.created_at DESC";

        try {
            // Obtener las reservas desde la base de datos
            $reservas = DB::select($query);

            // Iterar sobre las reservas para agregar información adicional
            foreach ($reservas as $reserva) {
                $reserva->verificacionPago = (bool) $reserva->verificacionPago;
                if($estado == 'Cancelada'){
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
    public function approve(int $id)
    {
        try {
            // Consulta SQL para actualizar el estado de la reserva a "Confirmada"
            $query = 'UPDATE reservas SET
            estado_id = ?,
            updated_at = NOW()
            WHERE id = ?';

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
    public function reject(int $id)
    {
        try {
            // Consulta SQL para actualizar el estado de la reserva a "Rechazada"
            $query = 'UPDATE reservas SET
            estado_id = ?,
            updated_at = NOW()
            WHERE id = ?';

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

        $queryInsert = 'INSERT INTO cancelacion_bitacora (
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
