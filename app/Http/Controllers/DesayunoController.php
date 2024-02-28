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
            'nombre' => 'required|string',
            'precio' => 'required|integer',
        ]);

        // Consulta SQL para insertar el desayuno
        $queryInsert = 'INSERT INTO desayunos (nombre, precio, descripcion, created_at) VALUES (?, ?, ?, NOW())';

        $queryMultimedia = 'INSERT INTO desayunos_media (
        desayuno_id,
        url,
        created_at)
        VALUES (?, ?, NOW())';

        DB::beginTransaction();

        try {
            // Ejecutar la inserción del desayuno
            DB::insert($queryInsert, [
                $request->nombre,
                $request->precio,
                $request->descripcion ? $request->descripcion : "",
            ]);

            // Obtener el ID del desayuno
            $desayunoId = DB::getPdo()->lastInsertId();

            // Insertar archivos Multimedia del desayuno
            if ($request->hasFile('media')) {
                $archivos = $request->file('media');

                foreach ($archivos as $archivo) {
                    $path = $archivo->store('media', 'public');
                    DB::insert($queryMultimedia, [
                        $desayunoId,
                        $path,
                    ]);
                }
            }

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Desayuno creado exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

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
        $query = 'SELECT
        d.id, 
        d.nombre, 
        d.precio, 
        d.descripcion,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", dm.id, "url", dm.url))
            FROM desayunos_media dm 
            WHERE dm.desayuno_id = d.id AND dm.deleted_at IS NULL
        ) AS media,
        created_at
        FROM desayunos d
        WHERE d.deleted_at IS NULL
        ORDER BY d.created_at DESC';

        try {
            // Obtener desayunos desde la base de datos
            $desayunos = DB::select($query);

            foreach ($desayunos as $desayuno) {
                // Decodificar datos JSON
                $desayuno->media = json_decode($desayuno->media);
            }

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
        // Consulta SQL para obtener la decoración por ID
        $query = 'SELECT
        d.id, 
        d.nombre, 
        d.precio, 
        d.descripcion,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", dm.id, "url", dm.url))
            FROM desayunos_media dm 
            WHERE dm.desayuno_id = d.id AND dm.deleted_at IS NULL
        ) AS media,
        created_at
        FROM desayunos d
        WHERE d.id = ? AND d.deleted_at IS NULL';

        try {
            // Obtener el desayuno por ID desde la base de datos
            $desayuno = DB::select($query, [$id]);

            // Verificar si se encontró el desayuno
            if (!empty($desayuno)) {
                // Retornar respuesta con la información del desayuno

                $desayuno = $desayuno[0];

                $desayuno->media = json_decode($desayuno->media);

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
            'nombre' => 'required|string',
            'precio' => 'required|integer',
        ]);

        // Consulta SQL para actualizar el desayuno por ID
        $query = 'UPDATE decoraciones SET
        nombre = ?,
        precio = ?,
        descripcion = ?,
        updated_at = NOW()
        WHERE id = ?';

        $queryMultimedia = 'INSERT INTO decoracion_media (
        decoracion_id,
        url,
        created_at)
        VALUES (?, ?, NOW())';

        $queryDelMedia = 'UPDATE decoracion_media SET 
        deleted_at = now()
        WHERE id = ?';

        DB::beginTransaction();

        try {
            // Ejecutar la actualización del desayuno por ID
            DB::update($query, [
                $request->nombre,
                $request->precio,
                $request->descripcion ? $request->descripcion : "",
                $id,
            ]);

            if ($request->hasFile('media')) {
                $archivos = $request->file('media');

                foreach ($archivos as $archivo) {
                    $path = $archivo->store('media', 'public');
                    DB::insert($queryMultimedia, [
                        $id,
                        $path,
                    ]);
                }
            }

            $toDelete = $request->input('toDelete', []);

            foreach ($toDelete as $fileID) {
                DB::update($queryDelMedia, [$fileID]);
            }

            DB::commit();

            $urls = $request->input('urls', []);

            foreach ($urls as $url) {
                $filePath = public_path('storage/' . $url);

                // Verificar si el archivo existe antes de intentar eliminarlo
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            return response()->json([
                'message' => 'Desayuno actualizado exitosamente',
            ]);
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
