<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaMotivosController extends Controller
{
    /**
     * Obtiene todos los motivos de reserva.
     *
     * Esta función recupera todos los motivos de reserva de la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con los motivos de reserva o un mensaje de error en caso de fallo.
     */
    public function read()
    {
        // Consulta SQL para obtener los motivos de reserva
        $query = 'SELECT id, nombre FROM reserva_motivos WHERE deleted_at IS NULL';

        try {
            // Ejecutar la consulta
            $results = DB::select($query);

            // Retornar una respuesta exitosa con los motivos de reserva
            return response()->json($results, 200);
        } catch (\Exception $e) {
            // Retornar una respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los motivos de reserva',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
