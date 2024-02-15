<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesayunoController extends Controller
{
    /**
     * Crear Desayuno
     *
     * Este método se encarga de crear un nuevo desayuno en la base de datos.
     * La información del desayuno se recibe a través de una solicitud HTTP, se valida y se realiza la inserción de datos en la tabla correspondiente.
     *
     * @param Request $request Datos de entrada que incluyen información sobre el desayuno.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function create(Request $request)
    {
        $request->validate([
            'desayuno' => 'required|string',
        ]);

        // Consulta SQL para insertar el desayuno
        $query = 'INSERT INTO desayunos (desayuno, created_at) VALUES (?, NOW())';

        try {
            // Ejecutar la inserción del desayuno
            DB::insert($query, [$request->desayuno]);

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Desayuno creado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al crear el desayuno',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener Desayunos
     *
     * Este método se encarga de obtener la lista de desayunos desde la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la lista de desayunos o un mensaje de error en caso de fallo.
     */
    public function read()
    {
        // Consulta SQL para obtener desayunos
        $query = 'SELECT id, desayuno, created_at FROM desayunos WHERE deleted_at IS NULL ORDER BY created_at DESC';

        try {
            // Obtener desayunos desde la base de datos
            $desayunos = DB::select($query);

            // Retornar respuesta con la lista de desayunos
            return response()->json($desayunos, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los desayunos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar Desayuno por ID
     *
     * Este método se encarga de obtener la información de un desayuno específico mediante su ID desde la base de datos.
     *
     * @param int $id ID del desayuno a buscar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la información del desayuno o un mensaje de error en caso de fallo.
     */
    public function find($id)
    {
        // Consulta SQL para obtener el desayuno por ID
        $query = 'SELECT id, desayuno, created_at FROM desayunos WHERE id = ? AND deleted_at IS NULL';

        try {
            // Obtener el desayuno por ID desde la base de datos
            $desayuno = DB::select($query, [$id]);

            // Verificar si se encontró el desayuno
            if (!empty($desayuno)) {
                // Retornar respuesta con la información del desayuno
                return response()->json($desayuno[0], 200);
            } else {
                return response()->json([
                    'message' => 'Desayuno no encontrado',
                ], 404);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar el desayuno',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar Desayuno por ID
     *
     * Este método se encarga de actualizar la información de un desayuno específico mediante su ID en la base de datos.
     *
     * @param Request $request Datos de entrada que incluyen la información actualizada del desayuno.
     * @param int $id ID del desayuno que se actualizará.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'desayuno' => 'required|string',
        ]);

        // Consulta SQL para actualizar el desayuno por ID
        $query = 'UPDATE desayunos SET desayuno = ?, updated_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización del desayuno por ID
            $result = DB::update($query, [
                $request->desayuno,
                $id,
            ]);

            // Verificar si la actualización fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Desayuno actualizado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al actualizar el desayuno',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar el desayuno',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar Desayuno por ID
     *
     * Este método se encarga de marcar un desayuno como eliminado en la base de datos mediante su ID.
     *
     * @param int $id ID del desayuno que se eliminará.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar el desayuno como eliminado por ID
        $query = 'UPDATE desayunos SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar el desayuno como eliminado
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Desayuno eliminado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar el desayuno',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar el desayuno',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
