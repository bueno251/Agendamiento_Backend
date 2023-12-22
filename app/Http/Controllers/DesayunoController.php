<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesayunoController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'desayuno' => 'required|string'
        ]);

        $query = 'INSERT INTO desayunos (
        desayuno,
        created_at)
        VALUES (?, NOW())';

        $desayuno = DB::insert($query, [
            $request->desayuno,
        ]);

        if ($desayuno) {
            return response()->json([
                'message' => 'Desayuno creado exitosamente',
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
        desayuno,
        created_at
        FROM desayunos
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC';

        $desayunos = DB::select($query);

        return response($desayunos, 200);
    }

    public function find($id)
    {
        $query = 'SELECT
        id,
        desayuno,
        created_at
        FROM desayunos
        WHERE id = ? && deleted_at IS NULL 
        ORDER BY created_at DESC';

        $desayunos = DB::select($query, [
            $id
        ]);

        return response($desayunos, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'desayuno' => 'required'
        ]);

        $query = 'UPDATE desayunos SET 
        desayuno = ?,
        updated_at = now()
        WHERE id = ?';

        $desayuno = DB::update($query, [
            $request->desayuno,
            $id
        ]);

        if ($desayuno) {
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
        $query = 'UPDATE desayunos SET 
        deleted_at = now()
        WHERE id = ?';

        $desayuno = DB::update($query, [
            $id
        ]);

        if ($desayuno) {
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
