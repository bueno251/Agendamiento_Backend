<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DayController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'dia' => 'required'
        ]);

        $query = 'INSERT INTO days (dia) VALUES (?)';

        DB::insert($query, [$request->dia]);

        return response('Dia ocupado exitosamente', 200);
    }
    public function read()
    {
        $query = 'SELECT * FROM days';

        $dias = DB::select($query);

        return response($dias, 200);
    }
    public function find($id)
    {
        $query = 'SELECT * FROM days WHERE id = ?';

        $dias = DB::select($query, [$id]);

        return response($dias, 200);
    }
    // public function update(Request $request)
    // {
    // }
    public function delete(Request $request, $dia)
    {
        $query = 'DELETE FROM days WHERE dia = ?';

        DB::delete($query, [$dia]);

        return response("Dia desocupado", 200);
    }
}
