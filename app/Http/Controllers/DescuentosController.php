<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DescuentosController extends Controller
{
    /**
     * Crea un nuevo descuento.
     *
     * Esta función crea un nuevo descuento en la base de datos.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP que contiene los datos del nuevo descuento.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si el descuento se creó correctamente o si se produjo un error.
     */
    public function create(Request $request)
    {
        // Validar los datos de la solicitud
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
            // Ejecutar la inserción del descuento
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
        td.fecha_inicio AS fechaInicio,
        td.fecha_fin AS fechaFin,
        td.nombre,
        td.descuento,
        td.activo,
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

    /**
     * Obtiene los descuentos aplicables a una habitación específica.
     *
     * Esta función busca en la base de datos los descuentos aplicables a una habitación específica.
     *
     * @param int $id El ID de la habitación para la que se buscan los descuentos.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con los descuentos aplicables si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function readByRoom($id)
    {
        // Consulta SQL para obtener el descuento por ID de la habitación
        $query = "SELECT
        td.id,
        td.fecha_inicio AS fechaInicio,
        td.fecha_fin AS fechaFin,
        td.nombre,
        td.descuento,
        td.tipo_id AS tipoId,
        tdt.tipo AS tipo
        FROM tarifa_descuentos td
        LEFT JOIN tarifa_descuento_tipos tdt ON tdt.id = td.tipo_id
        WHERE td.deleted_at IS NULL AND td.activo = 1
        AND FIND_IN_SET(?, REPLACE(REPLACE(td.habitaciones, '[', ''), ']', '')) > 0
        AND td.fecha_inicio >= CURDATE()";

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

    /**
     * Obtiene todos los tipos de descuentos.
     *
     * Esta función busca en la base de datos todos los tipos de descuentos disponibles.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con los tipos de descuentos si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function readTipos()
    {
        // Consulta SQL para obtener tipos de descuentos
        $query = 'SELECT
        id,
        tipo
        FROM tarifa_descuento_tipos
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC';

        try {
            // Ejecutar la consulta SQL para obtener tipos de descuentos
            $tiposDescuentos = DB::select($query);

            // Retornar respuesta con los tipos de descuentos si se encuentran
            return response()->json($tiposDescuentos, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los tipos de descuentos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene las habitaciones con los datos más simple para asignar.
     *
     * Esta función busca en la base de datos las habitaciones disponibles para asignar.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con las habitaciones disponibles si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function readRooms()
    {
        // Consulta SQL para obtener las habitaciones
        $query = 'SELECT
        id,
        nombre
        FROM room_padre
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC';

        try {
            // Ejecutar la consulta SQL para obtener las habitaciones
            $habitaciones = DB::select($query);

            // Retornar respuesta con las habitaciones si se encuentran
            return response()->json($habitaciones, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener las habitaciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza un descuento existente.
     *
     * Esta función actualiza un descuento existente en la base de datos.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP que contiene los datos actualizados del descuento.
     * @param int $id El ID del descuento que se va a actualizar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si el descuento se actualizó correctamente o si se produjo un error.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'fechaInicio' => 'required|string',
            'fechaFin' => 'required|string',
            'nombre' => 'required|string',
            'descuento' => 'required|integer',
            'habitaciones' => 'required|array',
            'activo' => 'required|boolean',
            'tipo' => 'required|integer',
            'user' => 'required|integer',
        ]);

        // Consulta SQL para actualizar el descuento por ID
        $query = 'UPDATE tarifa_descuentos SET
        fecha_inicio = ?,
        fecha_fin = ?,
        nombre = ?,
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
                $request->fechaInicio,
                $request->fechaFin,
                $request->nombre,
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

    /**
     * Elimina un descuento por su ID.
     *
     * Esta función marca como eliminado un descuento en la base de datos.
     *
     * @param int $id El ID del descuento que se va a eliminar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si el descuento se eliminó correctamente o si se produjo un error.
     */
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
