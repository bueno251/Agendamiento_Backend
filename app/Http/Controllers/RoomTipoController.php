<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RoomTipoController extends Controller
{
    /**
     * Crear Tipo de Habitación
     *
     * Este método se encarga de crear un nuevo tipo para una habitación en la base de datos.
     * La información del tipo se recibe a través de una solicitud HTTP, se valida y se realiza la inserción de datos en la tabla correspondiente.
     *
     * @param \Illuminate\Http\Request $request Datos de entrada que incluyen información sobre el tipo de la habitación.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function create(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string'
        ]);

        // Consulta SQL para insertar el tipo de la habitación
        $query = 'INSERT INTO room_tipos
        (tipo,
        created_at)
        VALUES (?, NOW())';

        try {
            // Ejecutar la inserción del tipo de la habitación
            DB::insert($query, [
                $request->tipo,
            ]);

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Tipo creado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al crear el tipo de la habitación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener Tipos de Habitación
     *
     * Este método se encarga de obtener los tipos de habitación almacenados en la base de datos.
     * Retorna un listado de tipos con su información.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la lista de tipos de habitación.
     */
    public function read()
    {
        // Consulta SQL para obtener los tipos de habitación
        $query = 'SELECT
        id,
        tipo,
        created_at
        FROM room_tipos
        WHERE deleted_at IS NULL
        ORDER BY 
        CASE WHEN created_at IS NULL THEN 0 ELSE 1 END, 
        created_at DESC';

        try {
            // Obtener los resultados de la consulta
            $tipos = DB::select($query);

            // Devolver la respuesta con los tipos de habitación
            return response()->json($tipos, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los tipos de habitación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar Tipo de Habitación por ID
     *
     * Este método se encarga de buscar un tipo de habitación por su identificador en la base de datos.
     * Retorna la información del tipo encontrado.
     *
     * @param int $id Identificador del tipo de habitación a buscar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la información del tipo de habitación encontrado.
     */
    public function find($id)
    {
        // Consulta SQL para obtener el tipo de habitación por ID
        $query = 'SELECT
        id,
        tipo,
        created_at
        FROM room_tipos
        WHERE id = ? && deleted_at IS NULL 
        ORDER BY created_at DESC';

        try {
            // Obtener el tipo de habitación por ID desde la base de datos
            $tipos = DB::select($query, [$id]);

            // Retornar respuesta con la información del tipo de habitación
            return response()->json($tipos, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar el tipo de habitación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar Tipo de Habitación por ID
     *
     * Este método se encarga de actualizar un tipo de habitación por su identificador en la base de datos.
     *
     * @param Request $request Datos de entrada que incluyen la nueva información del tipo de habitación.
     * @param int $id Identificador del tipo de habitación a actualizar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required'
        ]);

        // Consulta SQL para actualizar el tipo de habitación por ID
        $query = 'UPDATE room_tipos SET 
        tipo = ?,
        updated_at = now()
        WHERE id = ?';

        try {
            // Ejecutar la actualización del tipo de habitación por ID
            $result = DB::update($query, [
                $request->tipo,
                $id
            ]);

            // Verificar si la actualización fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Actualizado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al actualizar el tipo de habitación',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar el tipo de habitación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar Tipo de Habitación por ID
     *
     * Este método se encarga de marcar como eliminado un tipo de habitación por su identificador en la base de datos.
     *
     * @param int $id Identificador del tipo de habitación a eliminar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar el tipo de habitación como eliminado por ID
        $query = 'UPDATE room_tipos SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar el tipo de habitación como eliminado
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Eliminado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar el tipo de habitación',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar el tipo de habitación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
