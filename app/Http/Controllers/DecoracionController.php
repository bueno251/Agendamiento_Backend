<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DecoracionController extends Controller
{
    /**
     * Crear Decoración
     *
     * Este método se encarga de crear una nueva decoración en la base de datos.
     * La información de la decoración se recibe a través de una solicitud HTTP, se valida y se realiza la inserción de datos en la tabla correspondiente.
     *
     * @param Request $request Datos de entrada que incluyen información sobre la decoración.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function create(Request $request)
    {
        $request->validate([
            'decoracion' => 'required|string'
        ]);

        // Consulta SQL para insertar la decoración
        $query = 'INSERT INTO decoraciones (decoracion, created_at) VALUES (?, NOW())';

        try {
            // Ejecutar la inserción de la decoración
            DB::insert($query, [$request->decoracion]);

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Decoración creada exitosamente',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al crear la decoración',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener Decoraciones
     *
     * Este método se encarga de obtener la lista de decoraciones desde la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la lista de decoraciones o un mensaje de error en caso de fallo.
     */
    public function read()
    {
        // Consulta SQL para obtener decoraciones
        $query = 'SELECT id, decoracion, created_at FROM decoraciones WHERE deleted_at IS NULL ORDER BY created_at DESC';

        try {
            // Obtener decoraciones desde la base de datos
            $decoraciones = DB::select($query);

            // Retornar respuesta con la lista de decoraciones
            return response()->json($decoraciones, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener las decoraciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar Decoración por ID
     *
     * Este método se encarga de obtener la información de una decoración específica mediante su ID desde la base de datos.
     *
     * @param int $id ID de la decoración a buscar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la información de la decoración o un mensaje de error en caso de fallo.
     */
    public function find($id)
    {
        // Consulta SQL para obtener la decoración por ID
        $query = 'SELECT id, decoracion, created_at FROM decoraciones WHERE id = ? AND deleted_at IS NULL ORDER BY created_at DESC';

        try {
            // Obtener la decoración por ID desde la base de datos
            $decoracion = DB::select($query, [$id]);

            // Verificar si se encontró la decoración
            if (!empty($decoracion)) {
                // Retornar respuesta con la información de la decoración
                return response()->json($decoracion[0], 200);
            } else {
                return response()->json([
                    'message' => 'Decoración no encontrada',
                ], 404);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar la decoración',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar Decoración por ID
     *
     * Este método se encarga de actualizar la información de una decoración específica mediante su ID en la base de datos.
     *
     * @param Request $request Datos de entrada que incluyen la información actualizada de la decoración.
     * @param int $id ID de la decoración que se actualizará.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'decoracion' => 'required|string',
        ]);

        // Consulta SQL para actualizar la decoración por ID
        $query = 'UPDATE decoraciones SET decoracion = ?, updated_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización de la decoración por ID
            $result = DB::update($query, [
                $request->decoracion,
                $id,
            ]);

            // Verificar si la actualización fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Decoración actualizada exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al actualizar la decoración',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar la decoración',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar Decoración por ID
     *
     * Este método se encarga de marcar una decoración como eliminada en la base de datos mediante su ID.
     *
     * @param int $id ID de la decoración que se eliminará.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar la decoración como eliminada por ID
        $query = 'UPDATE decoraciones SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar la decoración como eliminada
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Decoración eliminada exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar la decoración',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar la decoración',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
