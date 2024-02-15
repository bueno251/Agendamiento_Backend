<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CaracteristicasController extends Controller
{
    /**
     * Crear Característica de Habitación
     *
     * Este método se encarga de crear una nueva característica de habitación en el sistema.
     * La información de la característica se recibe a través de una solicitud HTTP, se valida y se realiza la inserción de datos en la tabla 'room_caracteristicas'.
     * Se utiliza una transacción para garantizar la integridad de los datos y manejar cualquier error que pueda ocurrir durante el proceso.
     *
     * @param Request $request Datos de entrada que incluyen 'nombre' (string, obligatorio), 'descripcion' (string, obligatorio), 'icon' (string, obligatorio), 'estado' (integer, obligatorio).
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function create(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'icon' => 'required|string',
            'estado' => 'required|integer',
        ]);

        // Consulta SQL para insertar la característica de habitación
        $insertQuery = 'INSERT INTO room_caracteristicas (
        nombre,
        descripcion,
        url_icon,
        estado_id,
        created_at)
        VALUES (?, ?, ?, ?, NOW())';

        // Iniciar una transacción
        DB::beginTransaction();

        try {
            // Ejecutar la inserción de la característica de habitación
            DB::insert($insertQuery, [
                $request->nombre,
                $request->descripcion,
                $request->icon,
                $request->estado,
            ]);

            // Commit de la transacción
            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Característica creada exitosamente',
            ]);
        } catch (\Exception $e) {
            // Rollback en caso de error
            DB::rollBack();

            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al crear',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Leer Características de las Habitaciones
     *
     * Este método se encarga de recuperar las características de habitación desde la base de datos.
     * Devuelve una respuesta JSON con la información de las características de habitación disponibles.
     *
     * @return \Illuminate\Http\Response Respuesta JSON con la información de las características de habitación en caso de éxito, o un mensaje de error en caso de fallo.
     */
    public function read()
    {
        // Consulta SQL para obtener características de habitación con sus estados
        $query = 'SELECT
        rc.id,
        rc.nombre,
        rc.descripcion,
        rc.url_icon AS icon,
        rc.estado_id,
        rce.estado,
        rc.created_at
        FROM room_caracteristicas rc
        JOIN room_caracteristica_estados rce ON rce.id = rc.estado_id
        WHERE rc.deleted_at IS NULL
        ORDER BY rc.created_at DESC';

        try {
            // Obtener características de habitación desde la base de datos
            $caracteristicas = DB::select($query);

            // Retornar respuesta exitosa
            return response()->json($caracteristicas, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al obtener características de habitación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar Característica de Habitación por ID
     *
     * Este método se encarga de recuperar una característica de habitación específica mediante su ID desde la base de datos.
     * Devuelve una respuesta JSON con la información de la característica de habitación encontrada.
     *
     * @param int $id ID de la característica de habitación que se desea buscar.
     * @return \Illuminate\Http\Response Respuesta JSON con la información de la característica de habitación encontrada en caso de éxito, o un mensaje de error en caso de fallo.
     */
    public function find($id)
    {
        // Consulta SQL para obtener una característica de habitación por ID con su estado
        $query = 'SELECT
        rc.id,
        rc.nombre,
        rc.descripcion,
        rc.url_icon AS icon,
        rc.estado_id,
        rce.estado,
        rc.created_at
        FROM room_caracteristicas rc
        JOIN room_caracteristica_estados rce ON rce.id = rc.estado_id 
        WHERE rc.deleted_at IS NULL AND rc.id = ?
        ORDER BY rc.created_at DESC';

        try {
            // Obtener la característica de habitación por ID desde la base de datos
            $caracteristica = DB::select($query, [$id]);

            // Retornar respuesta exitosa
            return response()->json($caracteristica, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al buscar la característica',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar Característica de Habitación por ID
     *
     * Este método se encarga de actualizar una característica de habitación específica mediante su ID en la base de datos.
     * Devuelve una respuesta JSON indicando el éxito o un mensaje de error en caso de fallo.
     *
     * @param \Illuminate\Http\Request $request Datos de entrada que incluyen información como 'nombre' (string, obligatorio), 'descripcion' (string, obligatorio), 'icon' (string, obligatorio), 'estado' (integer, obligatorio).
     * @param int $id ID de la característica de habitación que se desea actualizar.
     * @return \Illuminate\Http\Response Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos de entrada
        $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'icon' => 'required|string',
            'estado' => 'required|integer',
        ]);

        // Consulta SQL para actualizar la característica de habitación por ID
        $query = 'UPDATE room_caracteristicas SET 
        nombre = ?,
        descripcion = ?,
        url_icon = ?,
        estado_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        try {
            // Ejecutar la actualización de la característica de habitación por ID
            $caracteristica = DB::update($query, [
                $request->nombre,
                $request->descripcion,
                $request->icon,
                $request->estado,
                $id
            ]);

            // Retornar respuesta de éxito o error
            return $caracteristica
                ? response()->json(['message' => 'Actualizado exitosamente'])
                : response()->json(['message' => 'Error al actualizar'], 500);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al actualizar la característica',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar Característica de Habitación por ID
     *
     * Este método se encarga de marcar como eliminada una característica de habitación específica mediante su ID en la base de datos.
     * Devuelve una respuesta JSON indicando el éxito o un mensaje de error en caso de fallo.
     *
     * @param int $id ID de la característica de habitación que se desea eliminar.
     * @return \Illuminate\Http\Response Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar como eliminada la característica de habitación por ID
        $query = 'UPDATE room_caracteristicas SET 
        deleted_at = NOW()
        WHERE id = ?';

        try {
            // Ejecutar la marcación como eliminada de la característica de habitación por ID
            $deleted = DB::update($query, [$id]);

            // Retornar respuesta de éxito o error
            return $deleted
                ? response()->json(['message' => 'Eliminado exitosamente'])
                : response()->json(['message' => 'Error al eliminar'], 500);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al eliminar la característica',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
