<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DivisasController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'required|string',
            'pais' => 'required|integer',
        ]);

        $query = 'INSERT INTO tarifas_divisas (
        nombre,
        codigo,
        pais_id,
        created_at)
        VALUES (?, ?, ?, NOW())';

        try {

            DB::insert($query, [
                $request->nombre,
                $request->codigo,
                $request->pais,
            ]);

            return response()->json([
                'message' => 'Divisa creada',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error inesperado al crear la divisa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function read()
    {
        $query = 'SELECT
        di.id,
        di.nombre,
        di.codigo,
        di.pais_id,
        p.nombre AS pais
        FROM tarifas_divisas di
        LEFT JOIN paises p ON p.id = di.pais_id
        WHERE di.deleted_at IS NULL
        ORDER BY di.created_at DESC';

        try {
            $tarifas_divisas = DB::select($query);

            return response()->json($divisas, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las divisas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function find($id)
    {
        $query = 'SELECT
        di.id,
        di.nombre,
        di.codigo,
        di.pais_id,
        p.nombre AS pais
        FROM tarifas_divisas di
        LEFT JOIN paises p ON p.id = di.pais_id
        WHERE p.deleted_at IS NULL AND p.id = ?';

        try {
            $divisa = DB::selectOne($query, [$id]);

            return response()->json($divisa, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al buscar la divisa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function default()
    {
        $query = 'SELECT
        di.id,
        di.nombre,
        di.codigo,
        di.pais_id,
        p.nombre AS pais
        FROM tarifas_divisas di
        LEFT JOIN paises p ON p.id = di.pais_id
        WHERE di.deleted_at IS NULL
        AND EXISTS (
            SELECT 1 FROM configuracion_defecto cd
            WHERE cd.divisa_id = di.id
        )';

        try {
            $divisa = DB::selectOne($query);

            return response()->json($divisa, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al buscar la divisa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'required|string',
            'pais' => 'required|integer',
        ]);

        $query = 'UPDATE tarifas_divisas SET 
        nombre = ?,
        codigo = ?,
        pais_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        try {
            $update = DB::update($query, [
                $request->nombre,
                $request->codigo,
                $request->pais,
                $id
            ]);

            return $update
                ? response()->json(['message' => 'Actualizado exitosamente'])
                : response()->json(['message' => 'Error al actualizar'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la divisa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        $query = 'UPDATE tarifas_divisas SET 
        deleted_at = NOW()
        WHERE id = ?';

        try {
            $deleted = DB::update($query, [$id]);

            return $deleted
                ? response()->json(['message' => 'Eliminado exitosamente'])
                : response()->json(['message' => 'Error al eliminar'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la divisa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
