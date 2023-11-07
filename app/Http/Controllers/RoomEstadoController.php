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

        DB::insert($query, [
            $request->estado,
        ]);

        return response('Estado creado exitosamente', 200);
    }

    public function read()
    {
        $query = 'SELECT
        id,
        estado,
        created_at
        FROM room_estados
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC';

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

        DB::update($query, [
            $request->estado,
            $id
        ]);

        return response('Estado actualizado exitosamente', 200);
    }

    public function delete($id)
    {
        $query = 'UPDATE room_estados SET 
        deleted_at = now()
        WHERE id = ?';

        DB::update($query, [
            $id
        ]);

        return response("Eliminado", 200);
    }
}
