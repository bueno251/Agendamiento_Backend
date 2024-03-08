<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartamentoController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'pais' => 'required|integer',
        ]);

        $query = 'INSERT INTO departamentos (
        nombre,
        pais_id)
        VALUES (?, ?)';

        try {

            DB::insert($query, [
                $request->nombre,
                $request->pais,
            ]);

            return response()->json([
                'message' => 'Departamento creado',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error inesperado al crear el departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function read($id)
    {
        $query = 'SELECT dp.id, dp.nombre FROM departamentos dp 
        WHERE dp.pais_id = ? AND dp.deleted_at IS NULL 
        AND EXISTS (
            SELECT 1 FROM municipios m 
            WHERE m.departamento_id = dp.id
        )
        ORDER BY dp.nombre ASC';

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

    public function find($id)
    {
        $query = 'SELECT
        dp.id,
        dp.nombre
        FROM departamentos dp
        WHERE dp.deleted_at IS NULL AND dp.id = ?';

        try {
            $departamento = DB::selectOne($query, [$id]);

            return response()->json($departamento, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al buscar el departamento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string',
            'pais' => 'required|integer',
        ]);

        $query = 'UPDATE departamentos SET 
        nombre = ?,
        pais_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        try {
            $update = DB::update($query, [
                $request->nombre,
                $request->pais,
                $id
            ]);

            return $update
                ? response()->json(['message' => 'Actualizado exitosamente'])
                : response()->json(['message' => 'Error al actualizar'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el departamento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        $query = 'UPDATE departamentos SET 
        deleted_at = NOW()
        WHERE id = ?';

        try {
            $deleted = DB::update($query, [$id]);

            return $deleted
                ? response()->json(['message' => 'Eliminado exitosamente'])
                : response()->json(['message' => 'Error al eliminar'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el departamento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
