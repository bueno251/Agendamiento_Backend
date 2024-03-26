<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImpuestoController extends Controller
{
    /**
     * Crea un nuevo impuesto.
     *
     * Esta función crea un nuevo impuesto en la base de datos.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP con los detalles del nuevo impuesto.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si el impuesto se creó correctamente o si se produjo un error.
     */
    public function create(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'required|string',
            'tasa' => 'required|integer',
            'tipo' => 'required|integer',
        ]);

        // Consulta SQL para insertar el nuevo impuesto
        $queryInsert = 'INSERT INTO tarifa_impuestos (
        nombre, 
        codigo,
        tasa,
        tipo_id,
        created_at)
        VALUES (?, ?, ?, ?, NOW())';

        // Iniciar una transacción de base de datos
        DB::beginTransaction();

        try {
            // Ejecutar la inserción del nuevo impuesto
            DB::insert($queryInsert, [
                $request->nombre,
                $request->codigo,
                $request->tasa,
                $request->tipo,
            ]);

            // Confirmar la transacción si la inserción fue exitosa
            DB::commit();

            // Retornar una respuesta de éxito si el impuesto se creó correctamente
            return response()->json([
                'message' => 'Impuesto creado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            // Revertir la transacción si se produjo un error durante la inserción
            DB::rollBack();

            // Retornar una respuesta de error con detalles si ocurrió un error
            return response()->json([
                'message' => 'Error al crear el impuesto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene la lista de impuestos.
     *
     * Esta función obtiene la lista de impuestos desde la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con la lista de impuestos si se obtienen correctamente, de lo contrario, devuelve un mensaje de error.
     */
    public function read()
    {
        // Consulta SQL para obtener la lista de impuestos
        $query = 'SELECT
        im.id, 
        im.nombre, 
        im.codigo,
        im.tasa,
        JSON_OBJECT(
            "id", it.id,
            "tipo", it.tipo
        ) AS tipo,
        im.created_at
        FROM tarifa_impuestos im
        JOIN tarifas_impuesto_tipos it ON it.id = im.tipo_id
        WHERE im.deleted_at IS NULL
        ORDER BY im.created_at DESC';

        try {
            // Ejecutar la consulta SQL para obtener la lista de impuestos
            $impuestos = DB::select($query);

            // Decodificar el objeto JSON del tipo de impuesto en cada impuesto
            foreach ($impuestos as $impuesto) {
                $impuesto->tipo = json_decode($impuesto->tipo);
            }

            // Retornar una respuesta con la lista de impuestos si se obtienen correctamente
            return response()->json($impuestos, 200);
        } catch (\Exception $e) {
            // Retornar una respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los impuestos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene la lista de tipos de impuesto.
     *
     * Esta función obtiene la lista de tipos de impuesto desde la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con la lista de tipos de impuesto si se obtienen correctamente, de lo contrario, devuelve un mensaje de error.
     */
    public function readTipos()
    {
        // Consulta SQL para obtener la lista de tipos de impuesto
        $query = 'SELECT
        it.id,
        it.tipo,
        it.created_at
        FROM tarifas_impuesto_tipos it
        WHERE it.deleted_at IS NULL
        ORDER BY it.created_at DESC';

        try {
            // Ejecutar la consulta SQL para obtener la lista de tipos de impuesto
            $tiposImpuesto = DB::select($query);

            // Retornar una respuesta con la lista de tipos de impuesto si se obtienen correctamente
            return response()->json($tiposImpuesto, 200);
        } catch (\Exception $e) {
            // Retornar una respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los tipos de impuesto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Encuentra un impuesto por su ID.
     *
     * Esta función busca un impuesto por su ID en la base de datos.
     *
     * @param int $id El ID del impuesto a buscar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con los detalles del impuesto si se encuentra, de lo contrario, devuelve un mensaje de error.
     */
    public function find($id)
    {
        // Consulta SQL para buscar el impuesto por su ID
        $query = 'SELECT
        im.id, 
        im.nombre, 
        im.codigo,
        im.tasa,
        JSON_OBJECT(
            "id", it.id,
            "tipo", it.tipo
        ) AS tipo,
        im.created_at
        FROM tarifa_impuestos im
        JOIN tarifas_impuesto_tipos it ON it.id = im.tipo_id
        WHERE im.id = ? AND im.deleted_at IS NULL';

        try {
            // Ejecutar la consulta SQL para buscar el impuesto por su ID
            $impuesto = DB::selectOne($query, [$id]);

            if ($impuesto) {
                // Decodificar el objeto JSON del tipo de impuesto si se encuentra el impuesto
                $impuesto->tipo = json_decode($impuesto->tipo);

                // Retornar una respuesta con los detalles del impuesto si se encuentra
                return response()->json($impuesto, 200);
            } else {
                // Retornar una respuesta de error si el impuesto no se encuentra
                return response()->json([
                    'message' => 'Impuesto no encontrado',
                ], 404);
            }
        } catch (\Exception $e) {
            // Retornar una respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar el impuesto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza un impuesto existente.
     *
     * Esta función actualiza un impuesto existente en la base de datos.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP con los datos del impuesto a actualizar.
     * @param int $id El ID del impuesto a actualizar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si la actualización fue exitosa o si ocurrió un error.
     */
    public function update(Request $request, $id)
    {
        // Validar los campos de la solicitud
        $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'required|string',
            'tasa' => 'required|integer',
            'tipo' => 'required|integer',
        ]);

        // Consulta SQL para actualizar el impuesto por su ID
        $query = 'UPDATE tarifa_impuestos SET
        nombre = ?,
        codigo = ?,
        tasa = ?,
        tipo_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        // Iniciar una transacción de base de datos
        DB::beginTransaction();

        try {
            // Ejecutar la actualización del impuesto por su ID
            DB::update($query, [
                $request->nombre,
                $request->codigo,
                $request->tasa,
                $request->tipo,
                $id,
            ]);

            // Confirmar la transacción si la actualización se realiza correctamente
            DB::commit();

            // Retornar una respuesta indicando que la actualización fue exitosa
            return response()->json([
                'message' => 'Impuesto actualizado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Deshacer la transacción si ocurre un error
            DB::rollBack();

            // Retornar una respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar el impuesto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina un impuesto por su ID.
     *
     * Esta función marca un impuesto como eliminado en la base de datos.
     *
     * @param int $id El ID del impuesto a eliminar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si la eliminación fue exitosa o si ocurrió un error.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar el impuesto como eliminado por su ID
        $query = 'UPDATE tarifa_impuestos SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar el impuesto como eliminado
            $delete = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($delete) {
                // Retornar una respuesta indicando que la eliminación fue exitosa
                return response()->json([
                    'message' => 'Impuesto eliminado exitosamente',
                ]);
            } else {
                // Retornar una respuesta de error si la eliminación no fue exitosa
                return response()->json([
                    'message' => 'Error al eliminar el impuesto',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar una respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar el impuesto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
