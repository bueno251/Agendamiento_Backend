<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarifasController extends Controller
{
    public function save(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'precio' => 'required|integer',
            'room' => 'required|integer',
        ]);

        $query = 'INSERT INTO tarifas (
            room_id,
            nombre,
            precio,
            jornada_id,
            created_at)
            VALUES (?, ?, ?, ?, now())
            ON DUPLICATE KEY UPDATE
            precio = VALUES(precio),
            jornada_id = VALUES(jornada_id),
            updated_at = NOW()';

        try {

            DB::insert($query, [
                $request->room,
                $request->name,
                $request->precio,
                $request->jornada,
            ]);

            return response()->json([
                'message' => 'Tarifa Guardada',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error inesperado al guardar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar o actualizar precios para una habitación específica.
     *
     * Este método se encarga de guardar o actualizar las tarifas para una habitación específica en diferentes días de la semana y jornadas.
     *
     * @param \Illuminate\Http\Request $request Datos de la solicitud.
     * @param int $id Identificador de la habitación.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación o detalles sobre un error inesperado.
     */
    public function saveTarifas(Request $request, int $id)
    {
        // Validar la solicitud
        $request->validate([
            'tieneIva' => 'required|boolean',
            'tarifas' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $tarifa) {
                        $validate = validator($tarifa, [
                            'name' => 'required|string',
                            'precio' => 'required|integer',
                        ]);

                        if ($validate->fails()) {
                            $fail('El formato de las tarifas es incorrecto: { name: string, precio: integer }');
                            break;
                        }
                    }
                }
            ],
        ]);

        // Consulta SQL para insertar o actualizar las tarifas
        $query = 'INSERT INTO tarifas (
        room_id,
        nombre,
        precio,
        precio_previo_festivo,
        jornada_id,
        impuesto_id,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, now())
        ON DUPLICATE KEY UPDATE
        precio = VALUES(precio),
        precio_previo_festivo = VALUES(precio_previo_festivo),
        jornada_id = VALUES(jornada_id),
        impuesto_id = VALUES(impuesto_id),
        updated_at = NOW()';

        // Obtener los datos de las tarifas
        $tarifas = $request->input("tarifas");

        // Iniciar la transacción de la base de datos
        DB::beginTransaction();

        try {

            // Iterar sobre las tarifas y ejecutar la consulta
            foreach ($tarifas as $day) {
                DB::insert($query, [
                    $id,
                    $day['name'],
                    $day['precio'],
                    isset($day['previoFestivo']) ? $day['previoFestivo'] : 0,
                    $day['jornada_id'],
                    $request->tieneIva ? $request->impuesto : null
                ]);
            }

            // Confirmar la transacción
            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'message' => 'Precios guardados exitosamente',
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Respuesta de error
            return response()->json([
                'message' => 'Error inesperado al guardar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tarifas de habitación
     *
     * Este método se encarga de obtener los tarifas de una habitación específica.
     *
     * @param int $roomId Identificador de la habitación.
     * @return array Resultado de la consulta con los tarifas de la habitación.
     */
    public function getTarifas(int $roomId)
    {
        $query = 'SELECT
        rt.nombre AS name,
        rt.precio AS precio,
        rt.precio_previo_festivo AS previoFestivo,
        tj.nombre AS jornada,
        rt.jornada_id AS jornada_id,
        CASE 
            WHEN rt.impuesto_id IS NOT NULL
                THEN ROUND(rt.precio * (1 + imp.tasa/100))
            ELSE ROUND(rt.precio)
        END AS precioConIva,
        CASE 
            WHEN rt.impuesto_id IS NOT NULL
                THEN ROUND(rt.precio_previo_festivo * (1 + imp.tasa/100))
            ELSE ROUND(rt.precio_previo_festivo)
        END AS previoFestivoConIva,
        rt.created_at AS created_at
        FROM tarifas rt
        LEFT JOIN tarifa_jornada tj ON tj.id = rt.jornada_id
        LEFT JOIN tarifa_impuestos imp ON imp.id = rt.impuesto_id
        WHERE rt.room_id = ? AND rt.deleted_at IS NULL
        ORDER BY
        FIELD(rt.nombre, "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Adicional", "Niños")';

        try {
            $tarifas = DB::select($query, [$roomId]);

            return response()->json($tarifas, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar las tarifas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        $query = 'UPDATE tarifas SET 
        deleted_at = NOW()
        WHERE id = ?';

        try {
            $deleted = DB::update($query, [$id]);

            return $deleted
                ? response()->json(['message' => 'Eliminado exitosamente'])
                : response()->json(['message' => 'Error al eliminar'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la tarifa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener Jornadas de Tarifas
     *
     * Este método recupera las jornadas de tarifas disponibles.
     *
     * @return array Devuelve un array de objetos con las jornadas de tarifas.
     */
    public function getJornadas()
    {
        // Consulta SQL para obtener las jornadas de tarifas
        $query = 'SELECT id, nombre FROM tarifa_jornada';

        // Obtener resultados de la consulta
        $tarifas = DB::select($query);

        // Devolver el array de objetos con las jornadas de tarifas
        return $tarifas;
    }
}
