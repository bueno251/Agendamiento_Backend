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
                'message' => 'Error al eliminar el país',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
