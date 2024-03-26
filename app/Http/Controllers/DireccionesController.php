<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DireccionesController extends Controller
{
    /**
     * Obtiene todos los países.
     *
     * Esta función busca en la base de datos todos los países disponibles.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con la lista de países si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function getPaises()
    {
        // Consulta SQL para obtener países
        $query = 'SELECT
        dp.id,
        dp.name AS nombre,
        dp.code AS codigo,
        dp.code_phone AS codigoTelefono
        FROM direcciones_paises dp
        ORDER BY dp.name ASC';

        try {
            // Ejecutar la consulta SQL para obtener países
            $paises = DB::select($query);

            // Retornar respuesta con la lista de países si se encuentran
            return response()->json($paises, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los países',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene los departamentos de un país específico.
     *
     * Esta función busca en la base de datos los departamentos asociados a un país específico.
     *
     * @param int $id El ID del país del cual se desean obtener los departamentos.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con la lista de departamentos si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function getDepartamentos($id)
    {
        // Consulta SQL para obtener departamentos por ID de país
        $query = 'SELECT
        dd.id,
        dd.name AS nombre
        FROM direcciones_departamentos dd
        WHERE dd.country_id = ?
        ORDER BY dd.name ASC';

        try {
            // Ejecutar la consulta SQL para obtener departamentos por ID de país
            $departamentos = DB::select($query, [$id]);

            // Retornar respuesta con la lista de departamentos si se encuentran
            return response()->json($departamentos, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los departamentos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene las ciudades de un departamento específico.
     *
     * Esta función busca en la base de datos las ciudades asociadas a un departamento específico.
     *
     * @param int $id El ID del departamento del cual se desean obtener las ciudades.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con la lista de ciudades si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function getCiudades($id)
    {
        // Consulta SQL para obtener ciudades por ID de departamento
        $query = 'SELECT
        dc.id,
        dc.name AS nombre
        FROM direcciones_ciudades dc
        WHERE dc.department_id = ?
        ORDER BY dc.name ASC';

        try {
            // Ejecutar la consulta SQL para obtener ciudades por ID de departamento
            $ciudades = DB::select($query, [$id]);

            // Retornar respuesta con la lista de ciudades si se encuentran
            return response()->json($ciudades, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener las ciudades',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
