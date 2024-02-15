<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Obtener un Usuario por ID
     *
     * Este método se encarga de obtener la información de un usuario específico por su identificador en la base de datos.
     *
     * @param int $id Identificador del usuario a obtener.
     * @return array|null Retorna la información del usuario como un array asociativo si se encuentra, o null si no se encuentra.
     */
    public static function getUser(int $id)
    {
        // Consulta SQL para obtener un usuario por ID
        $query = 'SELECT
        id,
        nombre,
        correo,
        created_at
        FROM users
        WHERE id = ? AND deleted_at IS NULL';

        try {
            // Ejecutar la consulta para obtener el usuario
            $users = DB::select($query, [$id]);

            // Verificar si se encontró el usuario
            if (!empty($users)) {
                // Retornar la información del usuario como un array asociativo
                return $users[0];
            } else {
                // Retornar null si el usuario no se encontró
                return null;
            }
        } catch (\Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante la consulta
            // Retornar null y, opcionalmente, registrar o notificar el error según la necesidad
            return null;
        }
    }
}
