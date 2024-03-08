<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarifasGeneralesController extends Controller
{
    public function save(Request $request)
    {
        $request->validate([
            'hasIva' => 'required|boolean',
            'tarifas' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $tarifa) {
                        $validate = validator($tarifa, [
                            'nombre' => 'required|string',
                            'precio' => 'required|integer',
                        ]);

                        if ($validate->fails()) {
                            $fail('El formato de las tarifas es incorrecto: { nombre: string, precio: integer }');
                            break;
                        }
                    }
                }
            ],
        ]);

        // Consulta SQL para insertar la tarifa
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

        // Obtener los datos de los días y jornadas
        $tarifas = $request->input("tarifas");

        DB::beginTransaction();

        try {
            foreach ($tarifas as $day) {
                DB::insert($query, [
                    $day['nombre'],
                    $day['precio'],
                    $request->hasIva ? $request->impuesto : null
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
                'message' => 'Error al guardas las tarifas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function read()
    {
        // Consulta SQL para obtener tarifas generales
        $query = 'SELECT
        te.id,
        te.nombre,
        te.precio,
        te.impuesto_id AS impuestoId,
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
        FROM tarifas_generales te
        LEFT JOIN impuestos imp ON imp.id = te.impuesto_id
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

    public function delete($id)
    {
        // Consulta SQL para marcar la tarifa como eliminado por ID
        $query = 'UPDATE tarifas_generales SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar la tarifa como eliminado
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
