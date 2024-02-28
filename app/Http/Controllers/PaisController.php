<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaisController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'nombreCorto' => 'required|string',
            'codigoTelefono' => 'required|integer',
        ]);

        $query = 'INSERT INTO paises (
        nombre,
        nombre_corto,
        codigo_telefono)
        VALUES (?, ?, ?)';

        try {

            DB::insert($query, [
                $request->nombre,
                $request->nombreCorto,
                $request->codigoTelefono,
            ]);

            return response()->json([
                'message' => 'Pais creado',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error inesperado al crear el país',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function read()
    {
        $query = 'SELECT
        p.id,
        p.nombre,
        p.nombre_corto AS nombreCorto,
        p.codigo_telefono AS codigoTelefono
        FROM paises p
        WHERE p.deleted_at IS NULL
        ORDER BY p.nombre ASC';

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

    public function find($id)
    {
        $query = 'SELECT
        p.id,
        p.nombre,
        p.nombre_corto AS nombreCorto,
        p.codigo_telefono AS codigoTelefono
        FROM paises p
        WHERE p.deleted_at IS NULL AND p.id = ?';

        try {
            $pais = DB::selectOne($query, [$id]);

            return response()->json($pais, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al buscar el país',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string',
            'nombreCorto' => 'required|string',
            'codigoTelefono' => 'required|integer',
        ]);

        $query = 'UPDATE paises SET 
        nombre = ?,
        nombre_corto = ?,
        codigo_telefono = ?,
        updated_at = NOW()
        WHERE id = ?';

        try {
            $pais = DB::update($query, [
                $request->nombre,
                $request->nombreCorto,
                $request->codigoTelefono,
                $id
            ]);

            return $pais
                ? response()->json(['message' => 'Actualizado exitosamente'])
                : response()->json(['message' => 'Error al actualizar'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el país',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        $query = 'UPDATE paises SET 
        deleted_at = NOW()
        WHERE id = ?';

        try {
            $deleted = DB::update($query, [$id]);

            return $deleted
                ? response()->json(['message' => 'Eliminado exitosamente'])
                : response()->json(['message' => 'Error al eliminar'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el país',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

