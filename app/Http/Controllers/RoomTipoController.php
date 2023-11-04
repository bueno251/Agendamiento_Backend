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

        DB::insert($query, [
            $request->tipo,
        ]);

        return response('tipo creado exitosamente', 200);
    }

    public function read()
    {
        $query = 'SELECT
        id,
        tipo,
        created_at
        FROM room_tipos
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC';

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

        DB::update($query, [
            $request->tipo,
            $id
        ]);

        return response('tipo creado exitosamente', 200);
    }

    public function delete($id)
    {
        $query = 'UPDATE room_tipos SET 
        deleted_at = now()
        WHERE id = ?';

        DB::update($query, [
            $id
        ]);

        return response("Eliminado", 200);
    }
}
