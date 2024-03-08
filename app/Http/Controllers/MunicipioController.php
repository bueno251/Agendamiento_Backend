<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MunicipioController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'departamento' => 'required|integer',
        ]);

        $query = 'INSERT INTO municipios (
        nombre,
        departamento_id)
        VALUES (?, ?)';

        try {

            DB::insert($query, [
                $request->nombre,
                $request->departamento,
            ]);

            return response()->json([
                'message' => 'Departamento creado',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error inesperado al crear el municipio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function read($id)
    {
        $query = 'SELECT dp.id, dp.nombre FROM municipios dp 
        WHERE dp.departamento_id = ? AND dp.deleted_at IS NULL
        ORDER BY dp.nombre ASC';

        try {
            $municipios = DB::select($query, [$id]);

            return response()->json($municipios, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los municipios',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function find($id)
    {
        $query = 'SELECT
        dp.id,
        dp.nombre
        FROM municipios dp
        WHERE dp.deleted_at IS NULL AND dp.id = ?';

        try {
            $departamento = DB::selectOne($query, [$id]);

            return response()->json($departamento, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al buscar el municipio',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string',
            'departamento' => 'required|integer',
        ]);

        $query = 'UPDATE municipios SET 
        nombre = ?,
        departamento_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        try {
            $update = DB::update($query, [
                $request->nombre,
                $request->departamento,
                $id
            ]);

            return $update
                ? response()->json(['message' => 'Actualizado exitosamente'])
                : response()->json(['message' => 'Error al actualizar'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el municipio',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        $query = 'UPDATE municipios SET 
        deleted_at = NOW()
        WHERE id = ?';

        try {
            $deleted = DB::update($query, [$id]);

            return $deleted
                ? response()->json(['message' => 'Eliminado exitosamente'])
                : response()->json(['message' => 'Error al eliminar'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el municipio',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
