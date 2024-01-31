<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CaracteristicasController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'icon' => 'required|string',
            'estado' => 'required|integer',
        ]);

        $query = 'INSERT INTO room_caracteristicas (
        nombre,
        descripcion,
        url_icon,
        estado_id,
        created_at)
        VALUES (?, ?, ?, ?, NOW())';

        DB::beginTransaction();

        try {

            DB::insert($query, [
                $request->nombre,
                $request->descripcion,
                $request->icon,
                $request->estado,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Caracteristica creada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear',
            ], 500);
        }
    }

    public function read()
    {
        $query = 'SELECT
        rc.id AS id,
        rc.nombre AS nombre,
        rc.descripcion AS descripcion,
        rc.url_icon AS icon,
        rc.estado_id AS estado_id,
        rce.estado AS estado,
        rc.created_at AS created_at
        FROM room_caracteristicas rc
        JOIN room_caracteristica_estados rce ON rce.id = rc.estado_id 
        WHERE rc.deleted_at IS NULL
        ORDER BY rc.created_at DESC';

        $caracteristicas = DB::select($query);

        return response($caracteristicas, 200);
    }

    public function find($id)
    {
        $query = 'SELECT
        rc.id AS id,
        rc.nombre AS nombre,
        rc.descripcion AS descripcion,
        rc.url_icon AS icon,
        rc.estado_id AS estado_id,
        rc.estado AS estado,
        rc.created_at AS created_at
        FROM room_caracteristicas rc
        JOIN room_caracteristica_estados rce ON rce.id = rc.estado_id 
        WHERE rc.deleted_at IS NULL AND rc.id = ?
        ORDER BY rc.created_at DESC';

        $caracteristica = DB::select($query, [
            $id
        ]);

        return response($caracteristica, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'icon' => 'required|string',
            'estado' => 'required|integer',
        ]);

        $query = 'UPDATE room_caracteristicas SET 
        nombre = ?,
        descripcion = ?,
        url_icon = ?,
        estado_id = ?,
        updated_at = now()
        WHERE id = ?';

        $caracteristica = DB::update($query, [
            $request->nombre,
            $request->descripcion,
            $request->icon,
            $request->estado,
            $id
        ]);

        if ($caracteristica) {
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
        $query = 'UPDATE room_caracteristicas SET 
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
