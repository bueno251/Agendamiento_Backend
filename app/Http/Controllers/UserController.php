<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public static function getUser(int $id)
    {
        $query = 'SELECT
        id,
        nombre,
        correo,
        created_at
        FROM users
        WHERE id = ? && deleted_at IS NULL';

        $users = DB::select($query, [
            $id
        ]);

        if (count($users) > 0) {
            return $users[0];
        } else {
            return null;
        }
    }
}
