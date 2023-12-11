<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RoomTipoController extends Controller
{
    public function create(Request $request)
    {

        $request->validate([
            'tipo' => 'required'
        ]);

        $query = 'INSERT INTO room_tipos
        (tipo,
        created_at)
        VALUES (?, NOW())';

        $tipo = DB::insert($query, [
            $request->tipo,
        ]);

        if ($tipo) {
            return response()->json([
                'message' => 'Tipo creado exitosamente',
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
        tipo,
        created_at
        FROM room_tipos
        WHERE deleted_at IS NULL
        ORDER BY 
        CASE WHEN created_at IS NULL THEN 0 ELSE 1 END, 
        created_at DESC';

        $tipos = DB::select($query);

        return response($tipos, 200);
    }

    public function find($id)
    {
        $query = 'SELECT
        id,
        tipo,
        created_at
        FROM room_tipos
        WHERE id = ? && deleted_at IS NULL 
        ORDER BY created_at DESC';

        $tipos = DB::select($query, [
            $id
        ]);

        return response($tipos, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required'
        ]);

        $query = 'UPDATE room_tipos SET 
        tipo = ?,
        updated_at = now()
        WHERE id = ?';

        $tipo = DB::update($query, [
            $request->tipo,
            $id
        ]);

        if ($tipo) {
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
        $query = 'UPDATE room_tipos SET 
        deleted_at = now()
        WHERE id = ?';

        $tipo = DB::update($query, [
            $id
        ]);

        if ($tipo) {
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
