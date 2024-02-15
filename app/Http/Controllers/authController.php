<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Autenticación del Usuario
     *
     * Este método se encarga de autenticar al usuario utilizando las credenciales proporcionadas.
     * Si las credenciales son válidas, se genera un token de acceso y se devuelve junto con la información del usuario.
     *
     * @param Request $request Datos de entrada que incluyen 'correo' (string, obligatorio) y 'password' (string, obligatorio).
     * @return \Illuminate\Http\Response Respuesta JSON con el token de acceso y la información del usuario en caso de éxito, o un mensaje de error en caso de credenciales inválidas.
     */
    public function login(Request $request)
    {
        // Validar las credenciales
        $credentials = $request->validate([
            'correo' => 'required',
            'password' => 'required',
        ]);

        // Intentar autenticar al usuario
        if (Auth::attempt($credentials)) {
            // Obtener el usuario autenticado
            $user = Auth::user();

            // Generar un token de acceso
            $token = $user->createToken('API Token')->plainTextToken;

            // Preparar la respuesta JSON
            $response = [
                'token' => $token,
                'user' => $user,
            ];

            // Retornar respuesta exitosa
            return response()->json($response, 200);
        } else {
            // Retornar respuesta de credenciales inválidas
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }
    }
}
