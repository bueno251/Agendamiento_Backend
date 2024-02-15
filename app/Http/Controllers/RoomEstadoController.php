<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RoomEstadoController extends Controller
{
    /**
     * Crear Estado de Habitación
     *
     * Este método se encarga de crear un nuevo estado para las habitaciones en la base de datos.
     *
     * @param Request $request Datos de entrada que incluyen información sobre el estado de la habitación.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function create(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'estado' => 'required|string'
        ]);

        // Consulta SQL para insertar el estado de la habitación
        $query = 'INSERT INTO room_estados
        (estado,
        created_at)
        VALUES (?, NOW())';

        try {
            // Ejecutar la inserción del estado de la habitación
            DB::insert($query, [
                $request->estado,
            ]);

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Estado creado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al crear el estado de la habitación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Leer Estados de Habitación
     *
     * Este método se encarga de obtener la lista de estados de las habitaciones desde la base de datos.
     *
     * @return \Illuminate\Http\Response Respuesta con los estados de habitación en formato JSON.
     */
    public function read()
    {
        // Consulta SQL para obtener estados de Habitación
        $query = 'SELECT
        id,
        estado,
        created_at
        FROM room_estados
        WHERE deleted_at IS NULL
        ORDER BY 
        CASE WHEN created_at IS NULL THEN 0 ELSE 1 END, 
        created_at DESC';

        try {
            // Obtener estados desde la base de datos
            $estados = DB::select($query);

            // Retornar respuesta con la lista de estados
            return response()->json($estados, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los estados',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar Estado de Habitación por ID
     *
     * Este método se encarga de obtener la información de un estado de habitación específico mediante su ID.
     *
     * @param int $id ID del estado de habitación a buscar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el estado de habitación encontrado.
     */
    public function find($id)
    {
        // Consulta SQL para obtener el estado por ID
        $query = 'SELECT
        id,
        estado,
        created_at
        FROM room_estados
        WHERE id = ? && deleted_at IS NULL 
        ORDER BY created_at DESC';

        try {
            // Obtener el estado por ID desde la base de datos
            $estado = DB::select($query, [$id]);

            // Verificar si se encontró el estado
            if (!empty($estado)) {
                // Retornar respuesta con la información del estado
                return response()->json($estado[0], 200);
            } else {
                return response()->json([
                    'message' => 'Estado no encontrado',
                ], 404);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar el estado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar Estado de Habitación por ID
     *
     * Este método se encarga de actualizar la información de un estado de habitación específico mediante su ID.
     *
     * @param Request $request Datos de entrada que incluyen la información actualizada del estado.
     * @param int $id ID del estado que se actualizará.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|string'
        ]);

        // Consulta SQL para actualizar el estado por ID
        $query = 'UPDATE room_estados SET estado = ?, updated_at = now() WHERE id = ?';

        try {
            // Ejecutar la actualización del estado por ID
            $result = DB::update($query, [
                $request->estado,
                $id,
            ]);

            // Verificar si la actualización fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Estado actualizado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al actualizar el estado',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar el estado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar Estado de Habitación por ID
     *
     * Este método se encarga de marcar como eliminado un estado de habitación mediante su ID.
     *
     * @param int $id ID del estado que se eliminará.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar el estado como eliminado por ID
        $query = 'UPDATE room_estados SET deleted_at = now() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar el estado como eliminado
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Estado eliminado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar el estado',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar el estado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
