<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'direccion' => 'required',
            'documento' => 'required',
            'correo' => 'required | email',
        ]);

        $query = 'INSERT INTO clients
        (nombre, direccion, documento, correo, observacion, created_at)
        VALUES (?, ?, ?, ?, ?, now())';

        DB::insert($query, [
            $request->nombre,
            $request->direccion,
            $request->documento,
            $request->correo,
            $request->observacion,
        ]);

        return response('Cliente creado exitosamente', 200);
    }

    public function read()
    {
        $query = 'SELECT id,
        nombre,
        direccion,
        documento,
        correo,
        observacion
        FROM clients WHERE deleted_at IS NULL';

        $clients = DB::select($query);

        return response($clients, 200);
    }

    public function find($id)
    {
        $query = 'SELECT * FROM clients WHERE id = ? && deleted_at IS NULL';

        $clients = DB::select($query, [$id]);

        return response($clients, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required',
            'direccion' => 'required',
            'documento' => 'required',
            'correo' => 'required',
        ]);

        $query = 'UPDATE clients SET 
        nombre = ?,
        direccion = ?,
        documento = ?,
        correo = ?,
        observacion = ?,
        updated_at = now(),
        WHERE id = ?';

        DB::update($query, [
            $request->nombre,
            $request->direccion,
            $request->documento,
            $request->correo,
            $request->observacion,
            $id
        ]);
    }

    public function delete($id)
    {

        $query = 'UPDATE clients SET 
        deleted_at = now(),
        WHERE id = ?';

        DB::update($query, [
            $id
        ]);

        return response("Eliminado", 200);
    }
}
