<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarifasOtasController extends Controller
{
    /**
     * Guardar Tarifa OTA
     *
     * Este método se encarga de guardar una nueva tarifa OTA o actualizar una existente en la base de datos.
     *
     * @param \Illuminate\Http\Request $request Objeto Request con los datos de la tarifa a guardar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function save(Request $request)
    {
        // Validación de los datos del formulario
        $request->validate([
            'room' => 'required|integer',
            'precio' => 'required|integer',
            'porcentaje' => 'required|boolean',
        ]);

        // Consulta SQL para insertar o actualizar la tarifa
        $query = 'INSERT INTO tarifas_otas (
        room_id,
        precio,
        es_porcentaje,
        created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        precio = VALUES(precio),
        es_porcentaje = VALUES(es_porcentaje),
        updated_at = NOW()';

        try {
            // Ejecutar la consulta de inserción o actualización
            DB::insert($query, [
                $request->room,
                $request->precio,
                $request->porcentaje,
            ]);

            // Respuesta exitosa
            return response()->json([
                'message' => 'Tarifa OTA Guardada',
            ], 200);
        } catch (\Exception $e) {
            // Respuesta de error en caso de excepción
            return response()->json([
                'message' => 'Error inesperado al guardar la tarifa OTA',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
