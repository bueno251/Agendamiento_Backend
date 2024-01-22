<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'nombre1' => 'required|string',
            'apellido1' => 'required|string',
            'correo' => 'required|email',
            'telefono' => 'required|integer',
        ]);

        $query = 'INSERT INTO clients
        (nombre1,
        nombre2,
        apellido1,
        apellido2,
        telefono,
        telefono_alt,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, now())';

        $queryUser = 'INSERT INTO users
        (nombre,
        correo,
        password,
        cliente_id,
        created_at)
        VALUES (?, ?, ?, ?, now())';

        DB::beginTransaction();

        try {
            DB::insert($query, [
                $request->nombre1,
                $request->nombre2,
                $request->apellido1,
                $request->apellido2,
                $request->telefono,
                $request->telefonoAlt,
            ]);

            $cliente_id = DB::getPdo()->lastInsertId();

            DB::insert($queryUser, [
                $request->username,
                $request->correo,
                Hash::make($request->password),
                $cliente_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Cliente creado exitosamente',
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function read()
    {
        $query = 'SELECT
        c.id AS id,
        c.nombre1 AS nombre1,
        c.nombre2 AS nombre2,
        c.apellido1 AS apellido1,
        c.apellido2 AS apellido2,
        CONCAT_WS(" ", c.nombre1, c.nombre2, c.apellido1, c.apellido2) AS fullname,
        c.tipo_documento_id AS tipo_documento_id,
        c.documento AS documento,
        c.direccion AS direccion,
        c.pais AS pais,
        c.departamento AS departamento,
        c.ciudad AS ciudad,
        u.correo AS correo,
        c.telefono AS telefono,
        c.telefono_alt AS telefono_alt,
        c.tipo_persona_id AS tipo_persona_id,
        c.tipo_obligacion_id AS tipo_obligacion_id,
        c.tipo_regimen_id AS tipo_regimen_id,
        c.observacion AS observacion,
        c.created_at AS created_at
        FROM clients c
        JOIN users u ON u.cliente_id = c.id
        WHERE c.deleted_at IS NULL
        ORDER BY c.created_at DESC';

        $clients = DB::select($query);

        return response($clients, 200);
    }

    public function find($id)
    {
        $query = 'SELECT 
        c.id AS id,
        c.nombre1 AS nombre1,
        c.nombre2 AS nombre2,
        c.apellido1 AS apellido1,
        c.apellido2 AS apellido2,
        CONCAT_WS(" ", c.nombre1, c.nombre2, c.apellido1, c.apellido2) AS fullname,
        c.tipo_documento_id AS tipo_documento_id,
        td.tipo AS tipo_documento,
        c.documento AS documento,
        c.direccion AS direccion,
        c.pais AS pais,
        c.departamento AS departamento,
        c.ciudad AS ciudad,
        c.correo AS correo,
        c.telefono AS telefono,
        c.telefono_alt AS telefono_alt,
        c.tipo_persona_id AS tipo_persona_id,
        tp.tipo AS tipo_persona,
        c.tipo_obligacion_id AS tipo_obligacion_id,
        cto.tipo AS tipo_obligacion,
        c.tipo_regimen_id AS tipo_regimen_id,
        tr.tipo AS tipo_regimen,
        c.observacion AS observacion,
        c.created_at AS created_at
        FROM clients c
        LEFT JOIN cliente_tipo_documento td ON c.tipo_documento_id = td.id
        LEFT JOIN cliente_tipo_persona tp ON c.tipo_persona_id = tp.id
        LEFT JOIN cliente_tipo_obligacion cto ON c.tipo_obligacion_id = cto.id
        LEFT JOIN cliente_tipo_regimen tr ON c.tipo_regimen_id = tr.id
        WHERE c.id = ? && c.deleted_at IS NULL';

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

    public function personalData(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string',
            'nombre1' => 'required|string',
            'apellido1' => 'required|string',
            'tipoDocumento' => 'required|integer',
            'documento' => 'required|integer',
            'correo' => 'required | email',
        ]);

        $query = 'UPDATE clients SET 
        nombre1 = ?,
        nombre2 = ?,
        apellido1 = ?,
        apellido2 = ?,
        tipo_documento_id = ?,
        documento = ?,
        updated_at = now()
        WHERE id = ?';

        $queryUser = 'UPDATE users SET 
        nombre = ?,
        correo = ?,
        updated_at = now()
        WHERE cliente_id = ?';

        DB::beginTransaction();

        try {
            $cliente = DB::update($query, [
                $request->nombre1,
                $request->nombre2,
                $request->apellido1,
                $request->apellido2,
                $request->tipoDocumento,
                $request->documento,
                $id
            ]);

            $user = DB::update($queryUser, [
                $request->username,
                $request->correo,
                $id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Actualizado exitosamente',
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function contactoData(Request $request, $id)
    {
        $request->validate([
            'direccion' => 'required|string',
            'pais' => 'required|string',
            'ciudad' => 'required|string',
            'telefono' => 'required|string',
        ]);

        $query = 'UPDATE clients SET
        direccion = ?,
        pais = ?,
        departamento = ?,
        ciudad = ?,
        telefono = ?,
        telefono_alt = ?,   
        updated_at = now()
        WHERE id = ?';

        $cliente = DB::update($query, [
            $request->direccion,
            $request->pais,
            $request->departamento,
            $request->ciudad,
            $request->telefono,
            $request->telefonoAlt,
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

    public function legalData(Request $request, $id)
    {
        $request->validate([
            'tipoPersona' => 'required|integer',
            'tipoObligacion' => 'required|integer',
            'tipoRegimen' => 'required|integer',
        ]);

        $query = 'UPDATE clients SET 
        tipo_persona_id = ?,
        tipo_obligacion_id = ?,
        tipo_regimen_id = ?,
        updated_at = now()
        WHERE id = ?';

        $cliente = DB::update($query, [
            $request->tipoPersona,
            $request->tipoObligacion,
            $request->tipoRegimen,
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
