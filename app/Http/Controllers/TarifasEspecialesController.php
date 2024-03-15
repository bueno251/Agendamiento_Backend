<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarifasEspecialesController extends Controller
{
    /**
     * Crear Tarifa Especial
     *
     * Este método se encarga de crear una nueva tarifa especial en la base de datos.
     *
     * @param \Illuminate\Http\Request $request Objeto Request con los datos de la tarifa especial.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function create(Request $request)
    {
        $request->validate([
            'fechaInicio' => 'required|string',
            'fechaFin' => 'required|string',
            'precio' => 'required|integer',
            'descripcion' => 'required|string',
            'room' => 'required|integer',
            'tieneIva' => 'required|boolean',
        ]);

        // Consulta SQL para insertar la tarifa especial
        $queryInsert = 'INSERT INTO tarifas_especiales (
        fecha_inicio,
        fecha_fin,
        precio,
        descripcion,
        room_id,
        impuesto_id,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())';

        DB::beginTransaction();

        try {
            // Ejecutar la inserción de la tarifa especial
            DB::insert($queryInsert, [
                $request->fechaInicio,
                $request->fechaFin,
                $request->precio,
                $request->descripcion,
                $request->room,
                $request->tieneIva ? $request->impuesto : null,
            ]);

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Tarifa especial creada exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al crear la tarifa especial',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener Tarifas Especiales
     *
     * Este método se encarga de recuperar las tarifas especiales asociadas a una habitación específica.
     *
     * @param int $id Identificador de la habitación.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con detalles sobre las tarifas especiales o un mensaje de error en caso de fallo.
     */
    public function read($id)
    {
        // Consulta SQL para obtener tarifas especiales
        $query = 'SELECT
        te.id,
        te.fecha_inicio AS fechaInicio,
        te.fecha_fin AS fechaFin,
        te.precio,
        te.descripcion,
        IF(te.impuesto_id IS NOT NULL, ROUND(te.precio * (1 + imp.tasa/100)), ROUND(te.precio)) AS precioConIva,
        IF(te.impuesto_id IS NOT NULL, ROUND(te.precio * (imp.tasa/100)), 0) AS precioIva,
        IF(te.impuesto_id IS NOT NULL, imp.tasa, 0) AS impuesto
        FROM tarifas_especiales te
        LEFT JOIN tarifa_impuestos imp ON imp.id = te.impuesto_id
        WHERE te.room_id = ? AND te.deleted_at IS NULL
        ORDER BY te.created_at DESC';

        try {
            // Obtener tarifas especiales desde la base de datos
            $tarifas_especiales = DB::select($query, [$id]);

            // Retornar respuesta con la lista de tarifas especiales
            return response()->json($tarifas_especiales, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener las tarifas especiales',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar Tarifa Especial por ID
     *
     * Este método se encarga de buscar y recuperar una tarifa especial específica según su ID.
     *
     * @param int $id Identificador de la tarifa especial.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con los detalles de la tarifa especial o un mensaje de error si no se encuentra.
     */
    public function find($id)
    {
        // Consulta SQL para obtener la tarifa especial por ID
        $query = 'SELECT
        te.id,
        te.fecha_inicio AS fechaInicio,
        te.fecha_fin AS fechaFin,
        te.precio,
        te.descripcion,
        IF(te.impuesto_id IS NOT NULL, ROUND(te.precio * (1 + im.tasa/100)), ROUND(te.precio)) AS precioConIva,
        IF(te.impuesto_id IS NOT NULL, ROUND(te.precio * (im.tasa/100)), 0) AS precioIva,
        IF(te.impuesto_id IS NOT NULL, im.tasa, 0) AS impuesto
        FROM tarifas_especiales te
        LEFT JOIN tarifa_impuestos im ON im.id = te.impuesto_id
        WHERE te.id = ? AND te.deleted_at IS NULL
        ORDER BY te.created_at DESC';

        try {
            // Obtener la tarifa especial por ID desde la base de datos
            $tarifa = DB::selectOne($query, [$id]);

            // Verificar si se encontró la tarifa especial
            if ($tarifa) {
                // Retornar respuesta con los detalles de la tarifa especial
                return response()->json($tarifa, 200);
            } else {
                // Retornar respuesta indicando que la tarifa especial no fue encontrada
                return response()->json([
                    'message' => 'Tarifa especial no encontrada',
                ], 404);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar la tarifa especial',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar Tarifa Especial por ID
     *
     * Este método se encarga de actualizar una tarifa especial específica según su ID.
     *
     * @param \Illuminate\Http\Request $request Objeto Request con los datos de la actualización.
     * @param int $id Identificador de la tarifa especial a actualizar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'fechaInicio' => 'required|string',
            'fechaFin' => 'required|string',
            'precio' => 'required|integer',
            'descripcion' => 'required|string',
        ]);

        // Consulta SQL para actualizar la tarifa especial por ID
        $query = 'UPDATE tarifas_especiales SET
        fecha_inicio = ?,
        fecha_fin = ?,
        precio = ?,
        descripcion = ?,
        updated_at = NOW()
        WHERE id = ?';

        DB::beginTransaction();

        try {
            // Ejecutar la actualización de la tarifa especial por ID
            DB::update($query, [
                $request->fechaInicio,
                $request->fechaFin,
                $request->precio,
                $request->descripcion,
                $id,
            ]);

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Tarifa especial actualizada exitosamente',
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar la tarifa especial',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar Tarifa Especial por ID
     *
     * Este método se encarga de marcar una tarifa especial como eliminada en la base de datos.
     *
     * @param int $id Identificador de la tarifa especial a eliminar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar la tarifa especial como eliminada por ID
        $query = 'UPDATE tarifas_especiales SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar la tarifa especial como eliminada
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Tarifa especial eliminada exitosamente',
                ]);
            } else {
                // Si no se realizó ninguna actualización, puede indicar que la tarifa no existía
                return response()->json([
                    'message' => 'La tarifa especial no existe o ya ha sido eliminada',
                ], 404);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar la tarifa especial',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
