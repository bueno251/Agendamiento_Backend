<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CancelacionTipoController extends Controller
{
    /**
     * Obtiene los detalles de cancelación de una reserva según el id de la reserva.
     *
     * Esta función busca en la base de datos los detalles de cancelación de una reserva
     * especificada por el ID de la reserva.
     *
     * @param int $id El ID de la reserva para la cual se desea obtener los detalles de cancelación.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con los detalles de cancelación si se encuentra, de lo contrario, devuelve un mensaje de error.
     */
    public static function cancelacionByReserva(int $id)
    {
        // Consulta SQL para obtener los detalles de cancelación de la reserva
        $query = 'SELECT
                cb.id AS id,
                cb.tipo_id AS tipo_id,
                ct.tipo AS tipo,
                cb.user_id AS user_id,
                us.nombre AS user,
                cb.nota_cancelacion AS motivo
            FROM
                reservas_cancelacion_bitacora cb
            LEFT JOIN
                reservas_cancelacion_tipos ct ON ct.id = cb.tipo_id
            LEFT JOIN
                users us ON us.id = cb.user_id
            WHERE
                cb.deleted_at IS NULL AND cb.reserva_id = ?
            ORDER BY
                cb.created_at DESC';

        try {
            // Ejecutar la consulta con el ID de la reserva proporcionado
            $cancelaciones = DB::select($query, [$id]);

            // Comprobar si se encontraron detalles de cancelación
            if (empty($cancelaciones)) {
                // Si no se encontraron detalles de cancelación, devolver una respuesta JSON con un mensaje de error
                return response()->json([
                    'message' => 'Cancelación inexistente',
                ], 404);
            }

            // Si se encontraron detalles de cancelación, devolver la primera entrada de los resultados
            return response()->json($cancelaciones[0], 200);
        } catch (\Exception $e) {
            // Si se produce algún error durante la ejecución de la consulta, devolver una respuesta JSON con un mensaje de error y el detalle del error.
            return response()->json([
                'message' => 'Error al obtener la cancelación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crea un nuevo tipo de cancelación de reserva.
     *
     * Esta función crea un nuevo tipo de cancelación de reserva en la base de datos.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP que contiene los datos del nuevo tipo de cancelación.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si el tipo se creó correctamente o si se produjo un error.
     */
    public function create(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'tipo' => 'required|string'
        ]);

        // Consulta SQL para insertar un nuevo tipo de cancelación
        $query = 'INSERT INTO reservas_cancelacion_tipos (tipo, created_at) VALUES (?, NOW())';

        try {
            // Ejecutar la consulta SQL con el tipo de cancelación proporcionado en la solicitud
            DB::insert($query, [$request->tipo]);

            // Si la inserción fue exitosa, devolver una respuesta JSON con un mensaje de éxito.
            return response()->json([
                'message' => 'Tipo creado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Si se produce algún error durante la inserción, devolver una respuesta JSON con un mensaje de error y el detalle del error.
            return response()->json([
                'message' => 'Error al crear el tipo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene todos los tipos de cancelación de reserva.
     *
     * Esta función busca en la base de datos todos los tipos de cancelación de reserva disponibles.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con los tipos de cancelación si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function read()
    {
        // Consulta SQL para obtener todos los tipos de cancelación de reserva no eliminados
        $query = 'SELECT id, tipo, created_at FROM reservas_cancelacion_tipos WHERE deleted_at IS NULL ORDER BY created_at DESC';

        try {
            // Ejecutar la consulta SQL para obtener los tipos de cancelación
            $reservas_cancelacion_tipos = DB::select($query);

            // Devolver una respuesta JSON con los tipos de cancelación si se encuentran
            return response()->json($reservas_cancelacion_tipos, 200);
        } catch (\Exception $e) {
            // Si se produce algún error durante la ejecución de la consulta, devolver una respuesta JSON con un mensaje de error y el detalle del error.
            return response()->json([
                'message' => 'Error al obtener los tipos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza un tipo de cancelación de reserva existente.
     *
     * Esta función actualiza un tipo de cancelación de reserva existente en la base de datos.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP que contiene los datos actualizados del tipo de cancelación.
     * @param int $id El ID del tipo de cancelación que se va a actualizar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si el tipo se actualizó correctamente o si se produjo un error.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'tipo' => 'required|string',
        ]);

        // Consulta SQL para actualizar el tipo de cancelación
        $query = 'UPDATE reservas_cancelacion_tipos SET tipo = ?, updated_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la consulta SQL con el tipo de cancelación actualizado y el ID proporcionado
            $result = DB::update($query, [
                $request->tipo,
                $id,
            ]);

            // Verificar si la actualización fue exitosa
            if ($result) {
                // Si la actualización fue exitosa, devolver una respuesta JSON con un mensaje de éxito.
                return response()->json([
                    'message' => 'Tipo actualizado exitosamente',
                ]);
            } else {
                // Si no se pudo actualizar el tipo, devolver una respuesta JSON con un mensaje de error.
                return response()->json([
                    'message' => 'Error al actualizar el tipo',
                ], 500);
            }
        } catch (\Exception $e) {
            // Si se produce algún error durante la ejecución de la consulta, devolver una respuesta JSON con un mensaje de error y el detalle del error.
            return response()->json([
                'message' => 'Error al actualizar el tipo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina un tipo de cancelación de reserva existente.
     *
     * Esta función marca como eliminado un tipo de cancelación de reserva existente en la base de datos.
     *
     * @param int $id El ID del tipo de cancelación que se va a eliminar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si el tipo se eliminó correctamente o si se produjo un error.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar como eliminado el tipo de cancelación
        $query = 'UPDATE reservas_cancelacion_tipos SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la consulta SQL para marcar como eliminado el tipo de cancelación con el ID proporcionado
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                // Si la eliminación fue exitosa, devolver una respuesta JSON con un mensaje de éxito.
                return response()->json([
                    'message' => 'Tipo eliminado exitosamente',
                ]);
            } else {
                // Si no se pudo eliminar el tipo, devolver una respuesta JSON con un mensaje de error.
                return response()->json([
                    'message' => 'Error al eliminar el tipo',
                ], 500);
            }
        } catch (\Exception $e) {
            // Si se produce algún error durante la ejecución de la consulta, devolver una respuesta JSON con un mensaje de error y el detalle del error.
            return response()->json([
                'message' => 'Error al eliminar el tipo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
