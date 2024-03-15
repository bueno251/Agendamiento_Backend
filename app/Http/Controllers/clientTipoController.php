<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class clientTipoController extends Controller
{
    /**
     * Obtener Tipos Utilizados por el Cliente
     *
     * Este método se encarga de obtener los tipos utilizados por el cliente desde diferentes tablas de la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con los tipos organizados por categoría, o un mensaje de error en caso de fallo.
     */
    public function read()
    {
        $query = '
        SELECT id, tipo, created_at, "documents" AS table_name
        FROM cliente_tipo_documento
        WHERE deleted_at IS NULL
        UNION
        SELECT id, tipo, created_at, "people" AS table_name
        FROM cliente_tipo_persona 
        WHERE deleted_at IS NULL
        UNION
        SELECT id, tipo, created_at, "obligations" AS table_name
        FROM cliente_tipo_obligacion 
        WHERE deleted_at IS NULL
        UNION
        SELECT id, tipo, created_at, "regimens" AS table_name
        FROM cliente_tipo_regimen
        WHERE deleted_at IS NULL';

        try {
            // Ejecutar la consulta
            $results = DB::select($query);

            $types = [];

            foreach ($results as $result) {
                // Organizar resultados por el nombre de la tabla
                $types[$result->table_name][] = (object) [
                    'id' => $result->id,
                    'tipo' => $result->tipo,
                    'created_at' => $result->created_at,
                ];
            }

            // Retornar respuesta exitosa
            return response()->json($types, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al traer los tipos utilizados por el cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene todos los tipos de documentos del cliente.
     *
     * Esta función busca en la base de datos todos los tipos de documentos del cliente disponibles.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con los tipos de documentos del cliente si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function documento()
    {
        // Consulta SQL para obtener todos los tipos de documentos del cliente no eliminados
        $query = '
        SELECT id, tipo
        FROM cliente_tipo_documento
        WHERE deleted_at IS NULL';

        try {
            // Ejecutar la consulta SQL para obtener los tipos de documentos del cliente
            $results = DB::select($query);

            // Devolver una respuesta JSON con los tipos de documentos del cliente si se encuentran
            return response()->json($results, 200);
        } catch (\Exception $e) {
            // Si se produce algún error durante la ejecución de la consulta, devolver una respuesta JSON con un mensaje de error y el detalle del error.
            return response()->json([
                'message' => 'Error al traer los tipos de documentos del cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
