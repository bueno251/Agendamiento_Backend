<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class clientTipoController extends Controller
{
    // public function createDocument(Request $request)
    // {
    //     $request->validate([
    //         'tipo' => 'required'
    //     ]);

    //     $query = 'INSERT INTO cliente_tipo_documento
    //     (tipo, created_at)
    //     VALUES (?, now())';

    //     DB::insert($query, [$request->tipo]);

    //     return response('tipo de documento creado', 200);
    // }

    // public function createObligacion(Request $request)
    // {
    //     $request->validate([
    //         'tipo' => 'required'
    //     ]);

    //     $query = 'INSERT INTO cliente_tipo_obligacion
    //     (tipo, created_at)
    //     VALUES (?, now())';

    //     DB::insert($query, [$request->tipo]);

    //     return response('tipo de obligacion creado', 200);
    // }

    // public function createPersona(Request $request)
    // {
    //     $request->validate([
    //         'tipo' => 'required'
    //     ]);

    //     $query = 'INSERT INTO cliente_tipo_persona
    //     (tipo, created_at)
    //     VALUES (?, now())';

    //     DB::insert($query, [$request->tipo]);

    //     return response('tipo de persona creado', 200);
    // }

    // public function createRegimen(Request $request)
    // {
    //     $request->validate([
    //         'tipo' => 'required'
    //     ]);

    //     $query = 'INSERT INTO cliente_tipo_persona
    //     (tipo, created_at)
    //     VALUES (?, now())';

    //     DB::insert($query, [$request->tipo]);

    //     return response('tipo de persona creado', 200);
    // }

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
            return response()->json($types);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al traer los tipos utilizados por el cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function readDoc()
    {
        $query = 'SELECT id, tipo FROM cliente_tipo_documento WHERE deleted_at IS NULL';

        $documents = DB::select($query);

        return response($documents, 200);
    }

    public function readObl()
    {
        $query = 'SELECT id, tipo FROM cliente_tipo_obligacion WHERE deleted_at IS NULL';

        $obligations = DB::select($query);

        return response($obligations, 200);
    }

    public function readPer()
    {
        $query = 'SELECT id, tipo FROM cliente_tipo_persona WHERE deleted_at IS NULL';

        $people = DB::select($query);

        return response($people, 200);
    }

    public function readReg()
    {
        $query = 'SELECT id, tipo FROM cliente_tipo_regimen WHERE deleted_at IS NULL';

        $regimens = DB::select($query);

        return response($regimens, 200);
    }
}
