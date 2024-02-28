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
            'nombre' => 'required|string',
            'precio' => 'required|integer',
        ]);

        // Consulta SQL para insertar la decoración
        $queryInsert = 'INSERT INTO decoraciones (nombre, precio, descripcion, created_at) VALUES (?, ?, ?, NOW())';

        $queryMultimedia = 'INSERT INTO decoracion_media (
        decoracion_id,
        url,
        created_at)
        VALUES (?, ?, NOW())';

        DB::beginTransaction();

        try {
            // Ejecutar la inserción de la decoración
            DB::insert($queryInsert, [
                $request->nombre,
                $request->precio,
                $request->descripcion ? $request->descripcion : "",
            ]);

            // Obtener el ID de la decoración
            $decoracionId = DB::getPdo()->lastInsertId();

            // Insertar archivos Multimedia a la decoración
            if ($request->hasFile('media')) {
                $archivos = $request->file('media');

                foreach ($archivos as $archivo) {
                    $path = $archivo->store('media', 'public');
                    DB::insert($queryMultimedia, [
                        $decoracionId,
                        $path,
                    ]);
                }
            }

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Decoración creada exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

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
        $query = 'SELECT
        d.id, 
        d.nombre, 
        d.precio, 
        d.descripcion,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", dm.id, "url", dm.url))
            FROM decoracion_media dm 
            WHERE dm.decoracion_id = d.id AND dm.deleted_at IS NULL
        ) AS media,
        created_at
        FROM decoraciones d
        WHERE d.deleted_at IS NULL
        ORDER BY d.created_at DESC';

        try {
            // Obtener decoraciones desde la base de datos
            $decoraciones = DB::select($query);

            foreach ($decoraciones as $decoracion) {
                // Decodificar datos JSON
                $decoracion->media = json_decode($decoracion->media);
            }

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
        $query = 'SELECT
        d.id, 
        d.nombre, 
        d.precio, 
        d.descripcion,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", dm.id, "url", dm.url))
            FROM decoracion_media dm 
            WHERE dm.decoracion_id = d.id AND dm.deleted_at IS NULL
        ) AS media,
        created_at
        FROM decoraciones d
        WHERE d.id = ? AND d.deleted_at IS NULL';

        try {
            // Obtener la decoración por ID desde la base de datos
            $decoracion = DB::select($query, [$id]);

            // Verificar si se encontró la decoración
            if (!empty($decoracion)) {
                // Retornar respuesta con la información de la decoración

                $decoracion = $decoracion[0];

                $decoracion->media = json_decode($decoracion->media);

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
            'nombre' => 'required|string',
            'precio' => 'required|integer',
        ]);

        // Consulta SQL para actualizar la decoración por ID
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
            // Ejecutar la actualización de la decoración por ID
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
                'message' => 'Decoración actualizada exitosamente',
            ]);
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
