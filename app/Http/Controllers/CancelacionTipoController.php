<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CancelacionTipoController extends Controller
{
    public static function cancelacionByReserva(int $id)
    {
        $query = 'SELECT
        cb.id AS id,
        cb.tipo_id AS tipo_id,
        ct.tipo AS tipo,
        cb.user_id AS user_id,
        us.nombre AS user,
        cb.nota_cancelacion AS motivo
        FROM cancelacion_bitacora cb
        LEFT JOIN cancelacion_tipos ct ON ct.id = cb.tipo_id
        LEFT JOIN users us ON us.id = cb.user_id
        WHERE cb.deleted_at IS NULL AND cb.reserva_id = ?
        ORDER BY cb.created_at DESC';

        try {
            $cancelaciones = DB::select($query, [$id]);

            if (empty($cancelaciones)) {
                return response()->json([
                    'message' => 'Cancelación inexistente',
                ], 404);
            }

            return response()->json($cancelaciones[0], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la cancelación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string'
        ]);

        $query = 'INSERT INTO cancelacion_tipos (tipo, created_at) VALUES (?, NOW())';

        try {
            DB::insert($query, [$request->tipo]);

            return response()->json([
                'message' => 'Tipo creado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el tipo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function read()
    {
        $query = 'SELECT id, tipo, created_at FROM cancelacion_tipos WHERE deleted_at IS NULL ORDER BY created_at DESC';

        try {
            $cancelacion_tipos = DB::select($query);

            return response()->json($cancelacion_tipos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los tipos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function find($id)
    {
        $query = 'SELECT id, tipo, created_at FROM cancelacion_tipos WHERE id = ? AND deleted_at IS NULL';

        try {
            $tipo = DB::select($query, [$id]);

            if (!empty($tipo)) {
                return response()->json($tipo[0], 200);
            } else {
                return response()->json([
                    'message' => 'Tipo no encontrado',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al buscar el tipo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|string',
        ]);

        $query = 'UPDATE cancelacion_tipos SET tipo = ?, updated_at = NOW() WHERE id = ?';

        try {
            $result = DB::update($query, [
                $request->tipo,
                $id,
            ]);

            if ($result) {
                return response()->json([
                    'message' => 'Tipo actualizado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al actualizar el tipo',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el tipo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        $query = 'UPDATE cancelacion_tipos SET deleted_at = NOW() WHERE id = ?';

        try {
            $result = DB::update($query, [$id]);

            if ($result) {
                return response()->json([
                    'message' => 'Tipo eliminado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar el tipo',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el tipo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
