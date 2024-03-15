<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarifasGeneralesController extends Controller
{
    /**
     * Guardar Tarifas Generales
     *
     * Este método se encarga de guardar las tarifas generales en la base de datos.
     *
     * @param \Illuminate\Http\Request $request Objeto Request con los datos de las tarifas generales.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function save(Request $request)
    {
        $request->validate([
            'tieneIva' => 'required|boolean',
            'tarifas' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $tarifa) {
                        // Validar cada tarifa individualmente
                        $validate = validator($tarifa, [
                            'nombre' => 'required|string',
                            'precio' => 'required|integer',
                        ]);

                        // Si la validación falla para alguna tarifa, se devuelve un mensaje de error
                        if ($validate->fails()) {
                            $fail('El formato de las tarifas es incorrecto: { nombre: string, precio: integer }');
                            break;
                        }
                    }
                }
            ],
        ]);

        // Consulta SQL para insertar o actualizar las tarifas
        $query = 'INSERT INTO tarifas_generales (
        nombre,
        precio,
        impuesto_id,
        created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        precio = VALUES(precio),
        impuesto_id = VALUES(impuesto_id),
        updated_at = NOW(),
        deleted_at = NULL';

        // Obtener los datos de las tarifas desde la solicitud
        $tarifas = $request->input("tarifas");

        DB::beginTransaction();

        try {
            // Iterar sobre cada tarifa y guardarla en la base de datos
            foreach ($tarifas as $tarifa) {
                DB::insert($query, [
                    $tarifa['nombre'],
                    $tarifa['precio'],
                    $request->tieneIva ? $request->impuesto : null
                ]);
            }

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Tarifas generales guardadas exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al guardar las tarifas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener todas las Tarifas Generales
     *
     * Este método se encarga de recuperar todas las tarifas generales almacenadas en la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la lista de tarifas generales.
     */
    public function read()
    {
        // Consulta SQL para obtener tarifas generales
        $query = 'SELECT
        te.id,
        te.nombre,
        te.precio,
        te.impuesto_id AS impuestoId,
        IF(te.impuesto_id IS NOT NULL, ROUND(te.precio * (1 + imp.tasa/100)), ROUND(te.precio)) AS precioConIva,
        IF(te.impuesto_id IS NOT NULL, ROUND(te.precio * (imp.tasa/100)), 0) AS precioIva,
        IF(te.impuesto_id IS NOT NULL, imp.tasa, 0) AS impuesto
        FROM tarifas_generales te
        LEFT JOIN tarifa_impuestos imp ON imp.id = te.impuesto_id
        WHERE te.deleted_at IS NULL';

        try {
            // Obtener tarifas generales desde la base de datos
            $tarifasGenerales = DB::select($query);

            // Retornar respuesta con la lista de tarifas generales
            return response()->json($tarifasGenerales, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener las tarifas generales',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina una tarifa general por su ID.
     *
     * @param int $id El ID de la tarifa que se eliminará.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar la tarifa como eliminada por su ID
        $query = 'UPDATE tarifas_generales SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la consulta SQL para marcar la tarifa como eliminada
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Tarifa general eliminada exitosamente',
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
