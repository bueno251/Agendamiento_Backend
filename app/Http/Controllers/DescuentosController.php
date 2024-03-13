<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DescuentosController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'fechaInicio' => 'required|string',
            'fechaFin' => 'required|string',
            'nombre' => 'required|string',
            'descuento' => 'required|integer',
            'habitaciones' => 'required|array',
            'tipo' => 'required|integer',
            'user' => 'required|integer',
        ]);

        // Consulta SQL para insertar el descuento
        $queryInsert = 'INSERT INTO tarifa_descuentos (
        fecha_inicio,
        fecha_fin,
        nombre,
        descuento,
        habitaciones,
        tipo_id,
        user_registro_id,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())';

        DB::beginTransaction();

        try {
            // Ejecutar la inserción de el descuento
            DB::insert($queryInsert, [
                $request->fechaInicio,
                $request->fechaFin,
                $request->nombre,
                $request->descuento,
                json_encode($request->habitaciones),
                $request->tipo,
                $request->user,
            ]);

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Descuento creada exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al crear el descuento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function read()
    {
        // Consulta SQL para obtener descuentos
        $query = 'SELECT
        td.id,
        td.fecha_inicio AS fechaInicio,
        td.fecha_fin AS fechaFin,
        td.nombre,
        td.descuento,
        td.habitaciones,
        td.tipo_id AS tipoId,
        tdt.tipo AS tipo,
        td.user_registro_id AS userRegistroId,
        td.created_at
        FROM tarifa_descuentos td
        LEFT JOIN tarifa_descuento_tipos tdt ON tdt.id = td.tipo_id
        WHERE td.deleted_at IS NULL
        ORDER BY td.created_at DESC';

        try {
            // Obtener descuentos desde la base de datos
            $result = DB::select($query);

            foreach ($result as $descuento) {

                // Decodificar datos JSON
                $descuento->habitaciones = json_decode($descuento->habitaciones);
            }

            // Retornar respuesta con la lista de descuentos
            return response()->json($result, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los descuentos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function readTipos()
    {
        $query = 'SELECT
        id,
        tipo
        FROM tarifa_descuento_tipos
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC';

        try {
            $tiposDescuentos = DB::select($query);

            return response()->json($tiposDescuentos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los tipos de descuentos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function readRooms()
    {
        $query = 'SELECT
        id,
        nombre
        FROM room_padre
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC';

        try {
            $descuentos = DB::select($query);

            return response()->json($descuentos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las habitaciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function find($id)
    {
        // Consulta SQL para obtener el descuento por ID
        $query = 'SELECT
        id,
        fecha_inicio AS fechaInicio,
        fecha_fin AS fechaFin,
        nombre,
        descuento,
        habitaciones,
        tipo_id AS tipoId,
        user_registro_id AS userRegistroId,
        created_at
        FROM tarifa_descuentos
        WHERE id = ? AND deleted_at IS NULL
        ORDER BY created_at DESC';

        try {
            // Obtener el descuento por ID desde la base de datos
            $result = DB::selectOne($query, [$id]);

            // Verificar si se encontró el descuento
            if ($result) {

                return response()->json($result, 200);
            } else {
                return response()->json([
                    'message' => 'Descuento no encontrado',
                ], 404);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar el descuento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fechaInicio' => 'required|string',
            'fechaFin' => 'required|string',
            'nombre' => 'required|string',
            'descuento' => 'required|integer',
            'habitaciones' => 'required|array',
            'tipo' => 'required|integer',
            'user' => 'required|integer',
        ]);

        // Consulta SQL para actualizar el descuento por ID
        $query = 'UPDATE tarifa_descuentos SET
        fecha_inicio = ?,
        fecha_fin = ?,
        nombre = ?,
        descuento = ?,
        habitaciones = ?,
        tipo_id = ?,
        user_actualizo_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        DB::beginTransaction();

        try {
            // Ejecutar la actualización de el descuento por ID
            DB::update($query, [
                $request->fechaInicio,
                $request->fechaFin,
                $request->nombre,
                $request->descuento,
                json_encode($request->habitaciones),
                $request->tipo,
                $request->user,
                $id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Descuento actualizado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar el descuento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        // Consulta SQL para marcar el descuento como eliminado por ID
        $query = 'UPDATE tarifa_descuentos SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar el descuento como eliminado
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Descuento eliminado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar el descuento',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar el descuento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
