<?php

namespace App\Http\Controllers\Reprogramacion;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReprogramacionMotivosController extends Controller
{
    /**
     * Obtener motivos de reprogramacion
     *
     * Este método se encarga de obtener la lista de motivos de reprogramacion desde la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la lista de motivos de reprogramacion o un mensaje de error en caso de fallo.
     */
    public function read()
    {
        // Consulta SQL para obtener motivos de reprogramacion
        $query = 'SELECT
        id,
        nombre
        FROM reservas_reprogramacion_motivos
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC';

        try {
            // Obtener decoraciones desde la base de datos
            $result = DB::select($query);

            // Retornar respuesta con la lista de decoraciones
            return response()->json($result, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los motivos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
