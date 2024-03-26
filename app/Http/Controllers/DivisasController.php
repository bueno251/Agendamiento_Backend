<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DivisasController extends Controller
{
    /**
     * Crea una nueva divisa.
     *
     * Esta función crea una nueva divisa en la base de datos.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP con los datos de la divisa a crear.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si la divisa se creó correctamente o si se produjo un error.
     */
    public function create(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'required|string',
            'pais' => 'required|integer',
        ]);

        // Consulta SQL para insertar la nueva divisa
        $query = 'INSERT INTO tarifas_divisas (
        nombre,
        codigo,
        pais_id,
        created_at)
        VALUES (?, ?, ?, NOW())';

        try {
            // Ejecutar la inserción de la nueva divisa
            DB::insert($query, [
                $request->nombre,
                $request->codigo,
                $request->pais,
            ]);

            // Retornar una respuesta de éxito
            return response()->json([
                'message' => 'Divisa creada exitosamente.',
            ], 200);
        } catch (\Exception $e) {
            // Retornar una respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al crear la divisa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene todas las divisas disponibles.
     *
     * Esta función busca en la base de datos todas las divisas disponibles junto con su país asociado.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con la lista de divisas si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function read()
    {
        // Consulta SQL para obtener las divisas
        $query = 'SELECT
        di.id,
        di.nombre,
        di.codigo,
        di.pais_id,
        p.name AS pais
        FROM tarifas_divisas di
        LEFT JOIN direcciones_paises p ON p.id = di.pais_id
        WHERE di.deleted_at IS NULL
        ORDER BY di.created_at DESC';

        try {
            // Ejecutar la consulta SQL para obtener las divisas
            $divisas = DB::select($query);

            // Retornar respuesta con la lista de divisas si se encuentran
            return response()->json($divisas, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener las divisas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Busca una divisa por su ID.
     *
     * Esta función busca en la base de datos una divisa específica por su ID, junto con el país asociado.
     *
     * @param int $id El ID de la divisa que se desea buscar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con los detalles de la divisa si se encuentra, de lo contrario, devuelve un mensaje de error.
     */
    public function find($id)
    {
        // Consulta SQL para buscar la divisa por ID
        $query = 'SELECT
        di.id,
        di.nombre,
        di.codigo,
        di.pais_id,
        p.name AS pais
        FROM tarifas_divisas di
        LEFT JOIN direcciones_paises p ON p.id = di.pais_id
        WHERE di.deleted_at IS NULL AND di.id = ?';

        try {
            // Ejecutar la consulta SQL para buscar la divisa por ID
            $divisa = DB::selectOne($query, [$id]);

            // Verificar si la divisa fue encontrada
            if ($divisa) {
                // Retornar respuesta con los detalles de la divisa si se encuentra
                return response()->json($divisa, 200);
            } else {
                // Retornar un mensaje de error si la divisa no fue encontrada
                return response()->json([
                    'message' => 'No se encontró la divisa con el ID proporcionado.',
                ], 404);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar la divisa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza una divisa existente.
     *
     * Esta función actualiza los detalles de una divisa existente en la base de datos.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP con los nuevos detalles de la divisa.
     * @param int $id El ID de la divisa que se desea actualizar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si la divisa se actualizó correctamente o si se produjo un error.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'required|string',
            'pais' => 'required|integer',
        ]);

        // Consulta SQL para actualizar la divisa por ID
        $query = 'UPDATE tarifas_divisas SET 
        nombre = ?,
        codigo = ?,
        pais_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        try {
            // Ejecutar la actualización de la divisa por ID
            DB::update($query, [
                $request->nombre,
                $request->codigo,
                $request->pais,
                $id
            ]);

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Descuento actualizado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar la divisa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina una divisa existente.
     *
     * Esta función marca como eliminada una divisa existente en la base de datos.
     *
     * @param int $id El ID de la divisa que se desea eliminar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si la divisa se eliminó correctamente o si se produjo un error.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar como eliminada la divisa por ID
        $query = 'UPDATE tarifas_divisas SET 
        deleted_at = NOW()
        WHERE id = ?';

        try {
            // Ejecutar la eliminación de la divisa por ID
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Divisa eliminada exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar la divisa',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar la divisa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
