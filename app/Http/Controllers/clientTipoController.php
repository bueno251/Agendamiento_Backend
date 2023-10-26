<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Cast\Array_;

class clientTipoController extends Controller
{
    public function createDocument(Request $request)
    {
        $request->validate([
            'tipo' => 'required'
        ]);

        $query = 'INSERT INTO cliente_tipo_documento
        (tipo, created_at)
        VALUES (?, now())';

        DB::insert($query, [$request->tipo]);

        return response('tipo de documento creado', 200);
    }
    
    public function createObligacion(Request $request)
    {
        $request->validate([
            'tipo' => 'required'
        ]);

        $query = 'INSERT INTO cliente_tipo_obligacion
        (tipo, created_at)
        VALUES (?, now())';

        DB::insert($query, [$request->tipo]);

        return response('tipo de obligacion creado', 200);
    }
    
    public function createPersona(Request $request)
    {
        $request->validate([
            'tipo' => 'required'
        ]);

        $query = 'INSERT INTO cliente_tipo_persona
        (tipo, created_at)
        VALUES (?, now())';

        DB::insert($query, [$request->tipo]);

        return response('tipo de persona creado', 200);
    }
    
    public function createRegimen(Request $request)
    {
        $request->validate([
            'tipo' => 'required'
        ]);

        $query = 'INSERT INTO cliente_tipo_persona
        (tipo, created_at)
        VALUES (?, now())';

        DB::insert($query, [$request->tipo]);

        return response('tipo de persona creado', 200);
    }

    public function read()
    {
        $queryDoc = 'SELECT id, tipo FROM cliente_tipo_documento WHERE deleted_at IS NULL';
        $queryObl = 'SELECT id, tipo FROM cliente_tipo_obligacion WHERE deleted_at IS NULL';
        $queryPer = 'SELECT id, tipo FROM cliente_tipo_persona WHERE deleted_at IS NULL';
        $queryReg = 'SELECT id, tipo FROM cliente_tipo_regimen WHERE deleted_at IS NULL';

        $documents = DB::select($queryDoc);
        $obligations = DB::select($queryObl);
        $people = DB::select($queryPer);
        $regimens = DB::select($queryReg);

        $json = array(
            "documents" => $documents,
            "obligations" => $obligations,
            "people" => $people,
            "regimens" => $regimens,
        );

        return response($json, 200);
    }
}
