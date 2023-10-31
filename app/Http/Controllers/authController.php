<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'correo' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $token = $user->createToken('API Token')->plainTextToken;

            $json = array(
                "token" => $token,
            );

            return response($json, 200);
        } else {
            return response('Credenciales inválidas', 401);
        }
    }
}
