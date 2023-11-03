<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    // public function create(Request $request)
    // {
    //     $request->validate([
    //         '' => 'required'
    //     ]);

    //     $query = 'INSERT INTO reservas
    //     (created_at)
    //     VALUES (NOW())';

    //     DB::insert($query);

    //     return response('Dia ocupado exitosamente', 200);
    // }
    // public function read()
    // {
    //     $query = 'SELECT * FROM dia_reservas WHERE deleted_at IS NULL';

    //     $dias = DB::select($query);

    //     return response($dias, 200);
    // }
    // public function find($id)
    // {
    //     $query = 'SELECT * FROM dia_reservas WHERE id = ? && deleted_at IS NULL';

    //     $dias = DB::select($query, [$id]);

    //     return response($dias, 200);
    // }
    // public function update(Request $request)
    // {
    // }
    // public function delete(Request $request, $dia)
    // {
    //     $query = 'DELETE FROM dia_reservas WHERE dia = ?';

    //     DB::delete($query, [$dia]);

    //     return response("Dia desocupado", 200);
    // }
}
