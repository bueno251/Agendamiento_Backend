<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RoomEstadoController extends Controller
{
    public function create(Request $request)
    {

        $request->validate([
            'estado' => 'required'
        ]);

        $query = 'INSERT INTO room_estados
        (estado,
        created_at)
        VALUES (?, NOW())';

        $estado = DB::insert($query, [
            $request->estado,
        ]);

        if ($estado) {
            return response()->json([
                'message' => 'Estado creado exitosamente',
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
        estado,
        created_at
        FROM room_estados
        WHERE deleted_at IS NULL
        ORDER BY 
        CASE WHEN created_at IS NULL THEN 0 ELSE 1 END, 
        created_at DESC';

        $estados = DB::select($query);

        return response($estados, 200);
    }

    public function find($id)
    {
        $query = 'SELECT
        id,
        estado,
        created_at
        FROM room_estados
        WHERE id = ? && deleted_at IS NULL 
        ORDER BY created_at DESC';

        $estados = DB::select($query, [
            $id
        ]);

        return response($estados, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required'
        ]);

        $query = 'UPDATE room_estados SET 
        estado = ?,
        updated_at = now()
        WHERE id = ?';

        $estado = DB::update($query, [
            $request->estado,
            $id
        ]);

        if ($estado) {
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
        $query = 'UPDATE room_estados SET 
        deleted_at = now()
        WHERE id = ?';

        $estado = DB::update($query, [
            $id
        ]);

        if ($estado) {
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
