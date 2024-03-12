<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DireccionesController extends Controller
{
    public function getPaises()
    {
        $query = 'SELECT
        dp.id,
        dp.name AS nombre,
        dp.code AS codigo,
        dp.code_phone AS codigoTelefono
        FROM direcciones_paises dp
        ORDER BY dp.name ASC';

        try {
            $paises = DB::select($query);

            return response()->json($paises, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los paises',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDepartamentos($id)
    {
        $query = 'SELECT
        dd.id,
        dd.name AS nombre
        FROM direcciones_departamentos dd
        WHERE dd.country_id = ?
        ORDER BY dd.name ASC';

        try {
            $departamentos = DB::select($query, [$id]);

            return response()->json($departamentos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los departamentos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCiudades($id)
    {
        $query = 'SELECT
        dc.id,
        dc.name AS nombre
        FROM direcciones_ciudades dc
        WHERE dc.department_id = ?
        ORDER BY dc.name ASC';

        try {
            $ciudades = DB::select($query, [$id]);

            return response()->json($ciudades, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los ciudades',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
