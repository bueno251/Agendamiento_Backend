<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\roomTipo;

class RoomTipoController extends Controller
{
    public function read()
    {
        $query = 'SELECT id, tipo FROM room_tipos WHERE deleted_at IS NULL';

        $tipos = DB::select($query);

        return response($tipos, 200);
    }
}
