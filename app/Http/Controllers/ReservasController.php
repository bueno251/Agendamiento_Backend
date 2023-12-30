<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservasController extends Controller
{
    public function reservaTemporal(Request $request)
    {

        $request->validate([
            'dateIn' => 'required|integer',
            'dateOut' => 'required|string',
            'room' => 'required|integer',
            'cliente' => 'required|integer',
            'user' => 'required|integer',
            'huespedes' => 'required|integer',
            'adultos' => 'required|integer',
            'niños' => 'required|integer',
            'precio' => 'required|integer',
            'abono' => 'required|integer',
            // 'comprobante' => 'required',
            'verificacion_pago' => 'required|integer',
        ]);

        $query = 'INSERT INTO reservas_temporales (
        fecha_entrada,
        fecha_salida,
        room_id,
        cliente_id,
        user_id,
        estado_id,
        cantidad_personas,
        precio,
        abono,
        comprobante,
        verificacion_pago,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        $reservaT = DB::insert($query, [
            $request->dateIn,
            $request->dateOut,
            $request->room,
            $request->cliente,
            $request->user,
            1,
            $request->huespedes,
            $request->precio,
            $request->abono,
            $request->comprobante,
            $request->verificacion_pago,
        ]);

        if ($reservaT) {
            return response()->json([
                'message' => 'reserva en espera por confirmación',
            ]);
        } else {
            return response()->json([
                'message' => 'Error al crear',
            ], 500);
        }
    }
}
