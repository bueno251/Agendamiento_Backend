<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImpuestoController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'required|string',
            'tasa' => 'required|integer',
            'tipo' => 'required|integer',
        ]);

        $queryInsert = 'INSERT INTO tarifa_impuestos (
            nombre, 
            codigo,
            tasa,
            tipo_id,
            created_at)
            VALUES (?, ?, ?, ?, NOW())';

        DB::beginTransaction();

        try {

            DB::insert($queryInsert, [
                $request->nombre,
                $request->codigo,
                $request->tasa,
                $request->tipo,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Impuesto creado exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear el impuesto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function read()
    {
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
            $impuestos = DB::select($query);

            foreach ($impuestos as $impuesto) {
                $impuesto->tipo = json_decode($impuesto->tipo);
            }

            return response()->json($impuestos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las impuestos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function readTipos()
    {
        $query = 'SELECT
        it.id,
        it.tipo,
        it.created_at
        FROM tarifas_impuesto_tipos it
        WHERE it.deleted_at IS NULL
        ORDER BY it.created_at DESC';

        try {
            $tiposImpuesto = DB::select($query);

            return response()->json($tiposImpuesto, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los tipos de impuesto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function find($id)
    {
        $query = 'SELECT
        im.id, 
        im.nombre, 
        im.codigo,
        im.tasa,
        JSON_OBJECT(
            "id", it.id,
            "tipo", it.tipo,
        ) AS tipo,
        im.created_at
        FROM tarifa_impuestos im
        JOIN tarifas_impuesto_tipos it ON it.id = im.tipo_id
        WHERE im.id = ? AND im.deleted_at IS NULL';

        try {
            $impuesto = DB::selectOne($query, [$id]);

            if ($impuesto) {

                $impuesto->tipo = json_decode($impuesto->tipo);

                return response()->json($impuesto, 200);
            } else {
                return response()->json([
                    'message' => 'Impuesto no encontrado',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al buscar el impuesto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'required|string',
            'tasa' => 'required|integer',
            'tipo' => 'required|integer',
        ]);

        $query = 'UPDATE tarifa_impuestos SET
        nombre = ?,
        codigo = ?,
        tasa = ?,
        tipo_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        DB::beginTransaction();

        try {
            DB::update($query, [
                $request->nombre,
                $request->codigo,
                $request->tasa,
                $request->tipo,
                $id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Impuesto actualizado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el impuesto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        // Consulta SQL para marcar el impuesto como eliminada por ID
        $query = 'UPDATE tarifa_impuestos SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar el impuesto como eliminada
            $delete = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($delete) {
                return response()->json([
                    'message' => 'Impuesto eliminado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar el impuesto',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar el impuesto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
