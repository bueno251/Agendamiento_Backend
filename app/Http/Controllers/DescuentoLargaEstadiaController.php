<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DescuentoLargaEstadiaController extends Controller
{
    public function create(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'nombre' => 'required|string',
            'dias' => 'required|integer',
            'descuento' => 'required|integer',
            'habitaciones' => 'required|array',
            'tipo' => 'required|integer',
            'user' => 'required|integer',
        ]);

        // Consulta SQL para insertar el descuento
        $queryInsert = 'INSERT INTO tarifa_descuentos_larga_estadia (
        nombre,
        dias_estadia,
        descuento,
        habitaciones,
        tipo_id,
        user_registro_id,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())';

        DB::beginTransaction();

        try {
            // Ejecutar la inserción del descuento
            DB::insert($queryInsert, [
                $request->nombre,
                $request->dias,
                $request->descuento,
                json_encode($request->habitaciones),
                $request->tipo,
                $request->user,
            ]);

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Descuento creado exitosamente',
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

    /**
     * Obtiene todos los descuentos.
     *
     * Esta función busca en la base de datos todos los descuentos disponibles.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con la lista de descuentos si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function read()
    {
        // Consulta SQL para obtener descuentos
        $query = 'SELECT
        td.id,
        td.nombre,
        td.dias_estadia AS dias,
        td.descuento,
        td.activo,
        td.habitaciones,
        td.tipo_id AS tipoId,
        tdt.tipo AS tipo,
        td.user_registro_id AS userRegistroId,
        td.created_at
        FROM tarifa_descuentos_larga_estadia td
        LEFT JOIN tarifa_descuento_tipos tdt ON tdt.id = td.tipo_id
        WHERE td.deleted_at IS NULL
        ORDER BY td.created_at DESC';

        try {
            // Ejecutar la consulta SQL para obtener descuentos
            $result = DB::select($query);

            // Decodificar datos JSON
            foreach ($result as $descuento) {
                $descuento->activo = (bool) $descuento->activo;
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

    public function readByRoom($id)
    {
        // Consulta SQL para obtener el descuento por ID de la habitación
        $query = "SELECT
        td.id,
        td.nombre,
        td.descuento,
        td.dias_estadia AS diasEstadia,
        td.tipo_id AS tipoId,
        tdt.tipo AS tipo
        FROM tarifa_descuentos_larga_estadia td
        LEFT JOIN tarifa_descuento_tipos tdt ON tdt.id = td.tipo_id
        WHERE td.deleted_at IS NULL AND td.activo = 1
        AND FIND_IN_SET(?, REPLACE(REPLACE(td.habitaciones, '[', ''), ']', '')) > 0";

        try {
            // Ejecutar la consulta SQL para obtener el descuento por ID de la habitación
            $result = DB::select($query, [$id]);

            // Retornar respuesta con los descuentos aplicables si se encuentran
            return response()->json($result, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar los descuentos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'nombre' => 'required|string',
            'dias' => 'required|integer',
            'descuento' => 'required|integer',
            'habitaciones' => 'required|array',
            'activo' => 'required|boolean',
            'tipo' => 'required|integer',
            'user' => 'required|integer',
        ]);

        // Consulta SQL para actualizar el descuento por ID
        $query = 'UPDATE tarifa_descuentos_larga_estadia SET
        nombre = ?,
        dias_estadia = ?,
        descuento = ?,
        activo = ?,
        habitaciones = ?,
        tipo_id = ?,
        user_actualizo_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        DB::beginTransaction();

        try {
            // Ejecutar la actualización del descuento por ID
            DB::update($query, [
                $request->nombre,
                $request->dias,
                $request->descuento,
                $request->activo,
                json_encode($request->habitaciones),
                $request->tipo,
                $request->user,
                $id,
            ]);

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Descuento actualizado exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

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
        $query = 'UPDATE tarifa_descuentos_larga_estadia SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar el descuento como eliminado
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Descuento eliminado exitosamente',
                ]);
            } else {
                // Devolver un mensaje de error si la eliminación no fue exitosa
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
