<?php

namespace App\Http\Controllers\Reprogramacion;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReprogramacionController extends Controller
{
    public function reprogramar(Request $request)
    {
        $request->validate([
            'reserva' => 'required|integer',
            'motivo' => 'required|integer',
            'nuevaFechaEntrada' => 'required|date',
            'nuevaFechaSalida' => 'required|date',
            'antiguaFechaEntrada' => 'required|date',
            'antiguaFechaSalida' => 'required|date',
        ]);

        $reprogramarReserva = 'UPDATE reservas SET
        fecha_entrada = ?,
        fecha_salida = ?,
        updated_at = NOW()
        WHERE id = ?';

        $insertReprogramacionBitacora = 'INSERT INTO reservas_reprogramacion_bitacora (
        reserva_id,
        motivo_id,
        nueva_fecha_entrada,
        nueva_fecha_salida,
        antigua_fecha_entrada,
        antigua_fecha_salida,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())';

        DB::beginTransaction();

        try {
            DB::insert($insertReprogramacionBitacora, [
                $request->reserva,
                $request->motivo,
                $request->nuevaFechaEntrada,
                $request->nuevaFechaSalida,
                $request->antiguaFechaEntrada,
                $request->antiguaFechaSalida,
            ]);
            
            DB::update($reprogramarReserva, [
                $request->nuevaFechaEntrada,
                $request->nuevaFechaSalida,
                $request->reserva,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Reserva Reprogramada Exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al reprogramar la reserva',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
