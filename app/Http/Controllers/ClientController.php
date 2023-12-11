<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'nombre1' => 'required',
            'apellido1' => 'required',
            'tipoDocumento' => 'required',
            'documento' => 'required',
            'direccion' => 'required',
            'pais' => 'required',
            'ciudad' => 'required',
            'correo' => 'required | email',
            'telefono' => 'required',
            'tipoPersona' => 'required',
            'tipoObligacion' => 'required',
            'tipoRegimen' => 'required',
        ]);

        if ($this->obtenerDoc($request->documento)) {
            return response()->json([
                'message' => 'Documento ya registrado',
            ], 500);
        }

        $query = 'INSERT INTO clients
        (nombre1,
        nombre2,
        apellido1,
        apellido2,
        tipo_documento_id,
        documento,
        direccion,
        pais,
        departamento,
        ciudad,
        correo,
        telefono,
        telefono_alt,
        tipo_persona_id,
        tipo_obligacion_id,
        tipo_regimen_id,
        observacion,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now())';

        $cliente = DB::insert($query, [
            $request->nombre1,
            $request->nombre2,
            $request->apellido1,
            $request->apellido2,
            $request->tipoDocumento,
            $request->documento,
            $request->direccion,
            $request->pais,
            $request->departamento,
            $request->ciudad,
            $request->correo,
            $request->telefono,
            $request->telefonoAlt,
            $request->tipoPersona,
            $request->tipoObligacion,
            $request->tipoRegimen,
            $request->observacion,
        ]);

        if ($cliente) {
            return response()->json([
                'message' => 'Cliente creado exitosamente',
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
        nombre1,
        nombre2,
        apellido1,
        apellido2,
        CONCAT_WS(" ", nombre1, nombre2, apellido1, apellido2) AS fullname,
        tipo_documento_id,
        documento,
        direccion,
        pais,
        departamento,
        ciudad,
        correo,
        telefono,
        telefono_alt,
        tipo_persona_id,
        tipo_obligacion_id,
        tipo_regimen_id,
        observacion,
        created_at
        FROM clients WHERE deleted_at IS NULL
        ORDER BY created_at DESC';

        $clients = DB::select($query);

        return response($clients, 200);
    }

    public function find($id)
    {
        $query = 'SELECT id,
        nombre1,
        nombre2,
        apellido1,
        apellido2,
        CONCAT_WS(" ", nombre1, nombre2, apellido1, apellido2) AS fullname,
        tipo_documento_id,
        documento,
        direccion,
        pais,
        departamento,
        ciudad,
        correo,
        telefono,
        telefono_alt,
        tipo_persona_id,
        tipo_obligacion_id,
        tipo_regimen_id,
        observacion,
        created_at
        FROM clients WHERE id = ? && deleted_at IS NULL';

        $clients = DB::select($query, [$id]);

        return response($clients, 200);
    }

    public function findDoc($doc)
    {
        $query = 'SELECT id,
        nombre1,
        nombre2,
        apellido1,
        apellido2,
        CONCAT_WS(nombre1, " ", nombre2, " ", apellido1, " ", apellido2) as fullname,
        tipo_documento_id,
        documento,
        direccion,
        pais,
        departamento,
        ciudad,
        correo,
        telefono,
        telefono_alt,
        tipo_persona_id,
        tipo_obligacion_id,
        tipo_regimen_id,
        observacion,
        created_at
        FROM clients WHERE documento = ? && deleted_at IS NULL';

        $clients = DB::select($query, [$doc]);

        return response($clients, 200);
    }

    public function obtenerDoc($doc)
    {
        $query = 'SELECT id
        FROM clients WHERE documento = ? && deleted_at IS NULL';

        $clients = DB::select($query, [$doc]);

        if (count($clients) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre1' => 'required',
            'apellido1' => 'required',
            'tipoDocumento' => 'required',
            'documento' => 'required',
            'direccion' => 'required',
            'pais' => 'required',
            'ciudad' => 'required',
            'correo' => 'required | email',
            'telefono' => 'required',
            'tipoPersona' => 'required',
            'tipoObligacion' => 'required',
            'tipoRegimen' => 'required',
        ]);

        $query = 'UPDATE clients SET 
        nombre1 = ?,
        nombre2 = ?,
        apellido1 = ?,
        apellido2 = ?,
        tipo_documento_id = ?,
        documento = ?,
        direccion = ?,
        pais = ?,
        departamento = ?,
        ciudad = ?,
        correo = ?,
        telefono = ?,
        telefono_alt = ?,
        tipo_persona_id = ?,
        tipo_obligacion_id = ?,
        tipo_regimen_id = ?,
        observacion = ?,
        updated_at = now()
        WHERE id = ?';

        $cliente = DB::update($query, [
            $request->nombre1,
            $request->nombre2,
            $request->apellido1,
            $request->apellido2,
            $request->tipoDocumento,
            $request->documento,
            $request->direccion,
            $request->pais,
            $request->departamento,
            $request->ciudad,
            $request->correo,
            $request->telefono,
            $request->telefonoAlt,
            $request->tipoPersona,
            $request->tipoObligacion,
            $request->tipoRegimen,
            $request->observacion,
            $id
        ]);

        if ($cliente) {
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
        $query = 'UPDATE clients SET 
        deleted_at = now()
        WHERE id = ?';

        $cliente = DB::update($query, [
            $id
        ]);

        if ($cliente) {
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
