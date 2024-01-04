<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservasController extends Controller
{
    public function createTemporal(Request $request)
    {

        $request->validate([
            'dateIn' => 'required|string',
            'dateOut' => 'required|string',
            'room' => 'required|integer',
            'user' => 'required|integer',
            'desayuno' => 'required|integer',
            'decoracion' => 'required|integer',
            'huespedes' => 'required|integer',
            'adultos' => 'required|integer',
            'niños' => 'required|integer',
            'precio' => 'required|integer',
            'abono' => 'required|integer',
            'verificacion_pago' => 'required|integer',
        ]);

        $query = 'INSERT INTO reservas_temporales (
        fecha_entrada,
        fecha_salida,
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
        abono,
        comprobante,
        verificacion_pago,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        $rutaArchivo = null;

        if ($request->hasFile('comprobante')) {
            $file = $request->file('comprobante');
            $rutaArchivo = $file->store('comprobantes', 'public'); // Almacenar el archivo en la carpeta 'comprobantes' del almacenamiento público
        }

        $reservaT = DB::insert($query, [
            $request->dateIn,
            $request->dateOut,
            $request->room,
            isset($request->cliente) ? $request->cliente : null,
            $request->user,
            1,
            $request->desayuno,
            $request->decoracion,
            $request->huespedes,
            $request->adultos,
            $request->niños,
            $request->precio,
            $request->abono,
            $rutaArchivo,
            $request->verificacion_pago,
        ]);

        if ($reservaT) {
            return response()->json([
                'message' => 'reserva por confirmar',
            ]);
        } else {
            return response()->json([
                'message' => 'Error al crear',
            ], 500);
        }
    }

    public function create(Request $request)
    {

        $request->validate([
            'dateIn' => 'required|string',
            'dateOut' => 'required|string',
            'room' => 'required|integer',
            'user' => 'required|integer',
            'desayuno' => 'required|integer',
            'decoracion' => 'required|integer',
            'huespedes' => 'required|integer',
            'adultos' => 'required|integer',
            'niños' => 'required|integer',
            'precio' => 'required|integer',
            'abono' => 'required|integer',
            'verificacion_pago' => 'required|integer',
        ]);

        $query = 'INSERT INTO reservas (
        fecha_entrada,
        fecha_salida,
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
        abono,
        comprobante,
        verificacion_pago,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        $rutaArchivo = null;

        if ($request->hasFile('comprobante')) {
            $file = $request->file('comprobante');
            $rutaArchivo = $file->store('comprobantes', 'public'); // Almacenar el archivo en la carpeta 'comprobantes' del almacenamiento público
        }

        $reservaT = DB::insert($query, [
            $request->dateIn,
            $request->dateOut,
            $request->room,
            isset($request->cliente) ? $request->cliente : null,
            $request->user,
            1,
            $request->desayuno,
            $request->decoracion,
            $request->huespedes,
            $request->adultos,
            $request->niños,
            $request->precio,
            $request->abono,
            $rutaArchivo,
            $request->verificacion_pago,
        ]);

        if ($reservaT) {
            return response()->json([
                'message' => 'Reserva Hecha',
            ]);
        } else {
            return response()->json([
                'message' => 'Error al crear',
            ], 500);
        }
    }

    public function readTemporales()
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
        FROM reservas_temporales r
        JOIN reserva_estados re ON r.estado_id = re.id
        JOIN desayunos desa ON r.desayuno_id = desa.id
        JOIN decoraciones deco ON r.decoracion_id = deco.id
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
        JOIN desayunos desa ON r.desayuno_id = desa.id
        JOIN decoraciones deco ON r.decoracion_id = deco.id
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

    public function approve(int $id){
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
    
    public function reject(int $id){
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
