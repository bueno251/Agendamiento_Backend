<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarifasEspecialesController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'fechaInicio' => 'required|string',
            'fechaFin' => 'required|string',
            'precio' => 'required|integer',
            'descripcion' => 'required|string',
            'room' => 'required|integer',
            'hasIva' => 'required|boolean',
        ]);

        // Consulta SQL para insertar la tarifa
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
            // Ejecutar la inserción de la tarifa
            DB::insert($queryInsert, [
                $request->fechaInicio,
                $request->fechaFin,
                $request->precio,
                $request->descripcion,
                $request->room,
                $request->hasIva ? $request->impuesto : null,
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
                'message' => 'Error al crear la tarifa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function read($id)
    {
        // Consulta SQL para obtener tarifas especiales
        $query = 'SELECT
        te.id,
        te.fecha_inicio AS fechaInicio,
        te.fecha_fin AS fechaFin,
        te.precio,
        te.descripcion,
        CASE
            WHEN te.impuesto_id
                THEN ROUND(te.precio * (1 + imp.tasa/100))
                ELSE ROUND(te.precio)
            END AS precioConIva,
        CASE
            WHEN te.impuesto_id
                THEN ROUND(te.precio * (imp.tasa/100))
                ELSE 0
            END AS precioIva,
        CASE
            WHEN te.impuesto_id
                THEN imp.tasa
                ELSE 0
            END AS impuesto
        FROM tarifas_especiales te
        LEFT JOIN impuestos imp ON imp.id = te.impuesto_id
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

    public function find($id)
    {
        // Consulta SQL para obtener la tarifa por ID
        $query = 'SELECT
        te.id,
        te.fecha_inicio AS fechaInicio,
        te.fecha_fin AS fechaFin,
        te.precio,
        te.descripcion,
        CASE
            WHEN te.impuesto_id
                THEN ROUND(te.precio * (1 + im.tasa/100))
                ELSE ROUND(te.precio)
            END AS precioConIva,
        CASE
            WHEN te.impuesto_id
                THEN ROUND(te.precio * (im.tasa/100))
                ELSE 0
            END AS precioIva,
        CASE
            WHEN te.impuesto_id
                THEN im.tasa
                ELSE 0
            END AS impuesto
        FROM tarifas_especiales te
        LEFT JOIN impuestos im ON im.id = te.impuesto_id
        WHERE te.id = ? AND te.deleted_at IS NULL
        ORDER BY te.created_at DESC';

        try {
            // Obtener la tarifa por ID desde la base de datos
            $tarifa = DB::selectOne($query, [$id]);

            // Verificar si se encontró la tarifa
            if ($tarifa) {

                return response()->json($tarifa, 200);
            } else {
                return response()->json([
                    'message' => 'Tarifa especial no encontrada',
                ], 404);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar la tarifa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fechaInicio' => 'required|string',
            'fechaFin' => 'required|string',
            'precio' => 'required|integer',
            'descripcion' => 'required|string',
        ]);

        // Consulta SQL para actualizar la tarifa por ID
        $query = 'UPDATE tarifas_especiales SET
        fecha_inicio = ?,
        fecha_fin = ?,
        precio = ?,
        descripcion = ?,
        updated_at = NOW()
        WHERE id = ?';

        DB::beginTransaction();

        try {
            // Ejecutar la actualización de la tarifa por ID
            DB::update($query, [
                $request->fechaInicio,
                $request->fechaFin,
                $request->precio,
                $request->descripcion,
                $id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Tarifa especial actualizado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar la tarifa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        // Consulta SQL para marcar la tarifa como eliminado por ID
        $query = 'UPDATE tarifas_especiales SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar la tarifa como eliminado
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Tarifa especial eliminada exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar la tarifa',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar la tarifa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
