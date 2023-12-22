<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DecoracionController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'decoracion' => 'required|string'
        ]);

        $query = 'INSERT INTO decoraciones (
        decoracion,
        created_at)
        VALUES (?, NOW())';

        $decoracion = DB::insert($query, [
            $request->decoracion,
        ]);

        if ($decoracion) {
            return response()->json([
                'message' => 'decoracion creado exitosamente',
            ]);
        } else {
            return response()->json([
                'message' => 'Error al crear',
            ], 500);
        }
    }

    public function read()
    {
        $query = 'SELECT
        id,
        decoracion,
        created_at
        FROM decoraciones
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC';

        $decoraciones = DB::select($query);

        return response($decoraciones, 200);
    }

    public function find($id)
    {
        $query = 'SELECT
        id,
        decoracion,
        created_at
        FROM decoraciones
        WHERE id = ? && deleted_at IS NULL 
        ORDER BY created_at DESC';

        $decoraciones = DB::select($query, [
            $id
        ]);

        return response($decoraciones, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'decoracion' => 'required'
        ]);

        $query = 'UPDATE decoraciones SET 
        decoracion = ?,
        updated_at = now()
        WHERE id = ?';

        $decoracion = DB::update($query, [
            $request->decoracion,
            $id
        ]);

        if ($decoracion) {
            return response()->json([
                'message' => 'Actualizado exitosamente',
            ]);
        } else {
            return response()->json([
                'message' => 'Error al actualizar',
            ], 500);
        }
    }

    public function delete($id)
    {
        $query = 'UPDATE decoraciones SET 
        deleted_at = now()
        WHERE id = ?';

        $decoracion = DB::update($query, [
            $id
        ]);

        if ($decoracion) {
            return response()->json([
                'message' => 'Eliminado exitosamente',
            ]);
        } else {
            return response()->json([
                'message' => 'Error al eliminar',
            ], 500);
        }
    }
}
