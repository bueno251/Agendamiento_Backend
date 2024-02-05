<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservasController extends Controller
{
    public function create(Request $request)
    {

        $request->validate([
            'cedula' => 'required|string',
            'telefono' => 'required|string',
            'dateIn' => 'required|string',
            'dateOut' => 'required|string',
            'room' => 'required|integer',
            'huespedes' => 'required|integer',
            'adultos' => 'required|integer',
            'niños' => 'required|integer',
            'precio' => 'required|integer',
            'verificacion_pago' => 'required|integer',
        ]);

        $query = "SELECT id 
        FROM reservas_temporales
        WHERE room_id = ?
        AND deleted_at IS NULL
        AND (
        fecha_entrada BETWEEN ? AND ?
        OR fecha_salida BETWEEN ? AND ?
        OR (fecha_entrada <= ? AND fecha_salida >= ?)
        )";

        $temporales = DB::select($query, [
            $request->room,
            $request->dateIn,
            $request->dateOut,
            $request->dateIn,
            $request->dateOut,
            $request->dateIn,
            $request->dateOut,
        ]);

        if (count($temporales) > 0) {
            return response()->json([
                'message' => 'Hay una reserva en proceso con esos dias, por favor, intentelo de nuevo más tarde',
            ], 400);
        }

        $query = 'INSERT INTO reservas_temporales (
        fecha_entrada,
        fecha_salida,
        cedula,
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
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        $reservaT = DB::insert($query, [
            $request->dateIn,
            $request->dateOut,
            $request->cedula,
            $request->telefono,
            $request->room,
            isset($request->cliente) ? $request->cliente : null,
            isset($request->user) ? $request->user : null,
            1,
            isset($request->desayuno) ? $request->desayuno : null,
            isset($request->decoracion) ? $request->decoracion : null,
            $request->huespedes,
            $request->adultos,
            $request->niños,
            $request->precio,
            $request->verificacion_pago,
        ]);

        $id = DB::getPdo()->lastInsertId();

        if ($reservaT) {
            return response()->json([
                'message' => 'Se espera el pago de su reserva dentro de los siguientes 10 minutos o sera eliminada su reserva',
                'reserva' => $id,
            ]);
        } else {
            return response()->json([
                'message' => 'Error al crear',
            ], 500);
        }
    }

    public function pagar(Request $request)
    {

        $request->validate([
            'reserva' => 'required|integer',
            'abono' => 'required|integer',
            'verificacion_pago' => 'required|integer',
        ]);

        $query = 'UPDATE reservas_temporales SET
        abono = ?,
        comprobante = ?,
        verificacion_pago = ?,
        updated_at = now()
        WHERE id = ? 
        AND deleted_at IS NULL';

        $rutaArchivo = null;

        if ($request->hasFile('comprobante')) {
            $file = $request->file('comprobante');
            $rutaArchivo = $file->store('comprobantes', 'public'); // Almacenar el archivo en la carpeta 'comprobantes' del almacenamiento público
        }

        DB::update($query, [
            $request->abono,
            $rutaArchivo,
            $request->verificacion_pago,
            $request->reserva,
        ]);

        return response()->json([
            'message' => 'Aprobando Reserva',
        ]);
    }

    public function getDates(Request $request)
    {
        $query = 'SELECT fecha_entrada, fecha_salida
        FROM reservas
        WHERE room_id = ?
        AND deleted_at IS NULL
        AND YEAR(fecha_entrada) >= YEAR(CURDATE()) 
        AND YEAR(fecha_salida) >= YEAR(CURDATE())';

        $dates = DB::select($query, [
            $request->id,
        ]);

        return response($dates);
    }

    public function read()
    {
        $query = 'SELECT
        r.id AS id,
        r.fecha_entrada AS fechaEntrada,
        r.fecha_salida AS fechaSalida,
        r.room_id AS room,
        r.cliente_id AS cliente,
        r.user_id AS user,
        r.estado_id AS estadoId,
        re.estado AS estado,
        r.desayuno_id AS desayunoId,
        desa.desayuno AS desayuno,
        r.decoracion_id AS decoracionId,
        deco.decoracion AS decoracion,
        r.huespedes AS huespedes,
        r.adultos AS adultos,
        r.niños AS niños,
        r.precio AS precio,
        r.abono AS abono,
        r.comprobante AS comprobante,
        r.verificacion_pago AS verificacionPago
        FROM reservas r
        JOIN reserva_estados re ON r.estado_id = re.id
        LEFT JOIN desayunos desa ON r.desayuno_id = desa.id
        LEFT JOIN decoraciones deco ON r.decoracion_id = deco.id
        WHERE r.deleted_at IS NULL
        ORDER BY r.created_at DESC';

        $reservas = DB::select($query);

        foreach ($reservas as $reserva) {
            $reserva->user = UserController::getUser($reserva->user);
            $reserva->room = RoomController::getRoom($reserva->room);
            $reserva->verificacionPago = $reserva->verificacionPago ? true : false;
        }

        return response($reservas, 200);
    }

    public function getReservaTemporal(int $room, int $user)
    {
        $query = 'SELECT
        id,
        fecha_entrada,
        fecha_salida,
        desayuno_id,
        decoracion_id,
        huespedes,
        adultos,
        niños,
        verificacion_pago
        FROM reservas_temporales
        WHERE user_id = ?
        AND room_id = ?
        AND deleted_at IS NULL';

        $reservaTemporal = DB::select($query, [
            $user,
            $room,
        ]);

        if (count($reservaTemporal) > 0) {
            $reservaTemporal[0]->verificacion_pago = $reservaTemporal[0]->verificacion_pago ? true : false;
        }

        return response($reservaTemporal);
    }

    public function reservaUser(int $id)
    {
        $query = 'SELECT
        r.id AS id,
        r.fecha_entrada AS fechaEntrada,
        r.fecha_salida AS fechaSalida,
        r.room_id AS room,
        r.cliente_id AS cliente,
        r.user_id AS user,
        r.estado_id AS estadoId,
        re.estado AS estado,
        r.desayuno_id AS desayunoId,
        desa.desayuno AS desayuno,
        r.decoracion_id AS decoracionId,
        deco.decoracion AS decoracion,
        r.huespedes AS huespedes,
        r.adultos AS adultos,
        r.niños AS niños,
        r.precio AS precio,
        r.abono AS abono,
        r.comprobante AS comprobante,
        r.verificacion_pago AS verificacionPago
        FROM reservas r
        JOIN reserva_estados re ON r.estado_id = re.id
        LEFT JOIN desayunos desa ON r.desayuno_id = desa.id
        LEFT JOIN decoraciones deco ON r.decoracion_id = deco.id
        WHERE r.user_id = ?
        AND r.deleted_at IS NULL
        ORDER BY r.fecha_entrada';

        $queryTemporales = 'SELECT
        r.id AS id,
        r.fecha_entrada AS fechaEntrada,
        r.fecha_salida AS fechaSalida,
        r.room_id AS room,
        r.cliente_id AS cliente,
        r.user_id AS user,
        r.estado_id AS estadoId,
        re.estado AS estado,
        r.desayuno_id AS desayunoId,
        desa.desayuno AS desayuno,
        r.decoracion_id AS decoracionId,
        deco.decoracion AS decoracion,
        r.huespedes AS huespedes,
        r.adultos AS adultos,
        r.niños AS niños,
        r.precio AS precio,
        r.abono AS abono,
        r.comprobante AS comprobante,
        r.verificacion_pago AS verificacionPago
        FROM reservas_temporales r
        JOIN reserva_estados re ON r.estado_id = re.id
        LEFT JOIN desayunos desa ON r.desayuno_id = desa.id
        LEFT JOIN decoraciones deco ON r.decoracion_id = deco.id
        WHERE r.user_id = ?
        AND r.deleted_at IS NULL';

        $reservas = DB::select($query, [$id]);
        $reservasTemporales = DB::select($queryTemporales, [$id]);

        return response()->json([
            'reservas' => $reservas,
            'temporales' => $reservasTemporales,
        ]);
    }

    public function approve(int $id)
    {
        $query = 'UPDATE reservas SET
        estado_id = ?,
        updated_at = now()
        WHERE id = ?';

        DB::update($query, [
            2,
            $id
        ]);

        return response()->json([
            'message' => 'Reserva Aprobada',
        ]);
    }

    public function reject(int $id)
    {
        $query = 'UPDATE reservas SET
        estado_id = ?,
        updated_at = now()
        WHERE id = ?';

        DB::update($query, [
            3,
            $id
        ]);

        return response()->json([
            'message' => 'Reserva Rechazada',
        ]);
    }
}
