<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaMotivosController extends Controller
{
    public function read()
    {
        $query = '
        SELECT id, nombre
        FROM reserva_motivos
        WHERE deleted_at IS NULL';

        try {
            // Ejecutar la consulta
            $results = DB::select($query);

            // Retornar respuesta exitosa
            return response()->json($results, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al traer los motivos de las reservas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
