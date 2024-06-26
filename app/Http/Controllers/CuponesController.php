<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuponesController extends Controller
{
    /**
     * Crea un nuevo cupón de descuento.
     *
     * @param Request $request La solicitud HTTP entrante.
     * @return JsonResponse La respuesta JSON indicando el resultado de la operación.
     */
    public function create(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'fechaInicio' => 'required|string',
            'fechaFin' => 'required|string',
            'nombre' => 'required|string',
            'descuento' => 'required|integer',
            'habitaciones' => 'required|array',
            'tipo' => 'required|integer',
            'precio' => 'required|integer',
            'cantidad' => 'required|integer',
            'user' => 'required|integer',
        ]);

        // Consulta SQL para insertar el cupón
        $queryInsertCupon = 'INSERT INTO tarifa_descuento_cupones (
        fecha_inicio,
        fecha_fin,
        cliente_id,
        nombre,
        descuento,
        habitaciones,
        tipo_id,
        precio_id,
        user_registro_id,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        // Consulta SQL para insertar los códigos del cupón
        $queryInsertCodigos = 'INSERT INTO tarifa_descuento_cupones_codigos (
        cupon_id,
        codigo,
        created_at)
        VALUES (?, ?, NOW())';

        DB::beginTransaction();

        try {
            // Ejecutar la inserción del cupón
            DB::insert($queryInsertCupon, [
                $request->fechaInicio,
                $request->fechaFin,
                $request->cliente,
                $request->nombre,
                $request->descuento,
                json_encode($request->habitaciones),
                $request->tipo,
                $request->precio,
                $request->user,
            ]);

            // Obtener el ID del cupón recién insertado
            $cuponId = DB::getPdo()->lastInsertId();

            // Insertar los códigos asociados al cupón
            for ($i = 0; $i < $request->cantidad; $i++) {
                DB::insert($queryInsertCodigos, [
                    $cuponId,
                    $this->generarCodigoAleatorio(6),
                ]);
            }

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Cupón creado exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al crear el cupón',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene todos los cupones.
     *
     * Esta función busca en la base de datos todos los cupones disponibles.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con la lista de descuentos si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function read()
    {
        // Consulta SQL para obtener descuentos
        $query = 'SELECT
        td.id,
        td.fecha_inicio AS fechaInicio,
        td.fecha_fin AS fechaFin,
        td.cliente_id AS clienteId,
        CONCAT_WS(" ", c.nombre1, c.apellido1) AS cliente,
        c.documento AS documento,
        td.nombre,
        td.descuento,
        td.activo,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "id", rt.id,
                "nombre", rt.nombre
            ))
            FROM room_padre rt
            WHERE FIND_IN_SET(rt.id, REPLACE(REPLACE(td.habitaciones, "[", ""), "]", "")) > 0
        ) AS habitaciones,
        td.tipo_id AS tipoId,
        tdt.tipo AS tipo,
        td.precio_id AS precioId,
        tdp.nombre AS precio,
        td.user_registro_id AS userRegistroId,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT(
                "id", tdcc.id,
                "codigo", tdcc.codigo,
                "activo", tdcc.activo = 1,
                "usado", tdcc.usado = 1
            ))
            FROM tarifa_descuento_cupones_codigos tdcc
            WHERE tdcc.cupon_id = td.id AND tdcc.deleted_at IS NULL
        ) AS codigos,
        td.created_at
        FROM tarifa_descuento_cupones td
        LEFT JOIN tarifa_descuento_tipos tdt ON tdt.id = td.tipo_id
        LEFT JOIN tarifa_descuento_precios tdp ON tdp.id = td.precio_id
        LEFT JOIN clients c ON c.id = td.cliente_id
        WHERE td.deleted_at IS NULL
        ORDER BY td.created_at DESC';

        try {
            // Ejecutar la consulta SQL para obtener cupones
            $result = DB::select($query);

            // Decodificar datos JSON
            foreach ($result as $cupon) {
                $cupon->activo = (bool) $cupon->activo;
                $cupon->habitaciones = json_decode($cupon->habitaciones);
                $cupon->codigos = json_decode($cupon->codigos);
            }

            // Retornar respuesta con la lista de cupones
            return response()->json($result, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los cupones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene los precios de la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse Retorna una respuesta JSON con los precios obtenidos.
     */
    public function getPrecios()
    {
        // Consulta SQL para seleccionar los precios de la tabla tarifa_descuento_precios
        $query = 'SELECT
        id,
        nombre
        FROM tarifa_descuento_precios
        WHERE deleted_at IS NULL';

        try {
            // Ejecutar la consulta SQL
            $result = DB::select($query);

            // Devolver una respuesta JSON con los precios obtenidos
            return response()->json($result, 200);
        } catch (\Exception $e) {
            // Manejar cualquier error que ocurra y devolver una respuesta de error JSON
            return response()->json([
                'message' => 'Error al obtener los precios del cupón',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene los cupones aplicables a una habitación específica.
     *
     * Esta función busca en la base de datos los cupones aplicables a una habitación específica.
     *
     * @param int $id El ID de la habitación para la que se buscan los cupones.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con los cupones aplicables si se encuentran, de lo contrario, devuelve un mensaje de error.
     */
    public function readByRoom($id)
    {
        // Consulta SQL para obtener el cupón por ID de la habitación
        $query = "SELECT
        td.id,
        td.fecha_inicio AS fechaInicio,
        td.fecha_fin AS fechaFin
        FROM tarifa_descuento_cupones td
        WHERE td.deleted_at IS NULL AND td.activo = 1
        AND FIND_IN_SET(?, REPLACE(REPLACE(td.habitaciones, '[', ''), ']', '')) > 0
        AND EXISTS (
            SELECT 1
            FROM tarifa_descuento_cupones_codigos tdcc
            WHERE tdcc.cupon_id = td.id
            AND tdcc.activo = 1
            AND tdcc.usado = 0
            AND tdcc.deleted_at IS NULL
        )";

        try {
            // Ejecutar la consulta SQL para obtener el cupón por ID de la habitación
            $result = DB::select($query, [$id]);

            // Retornar respuesta con los cupones aplicables si se encuentran
            return response()->json($result, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar los cupones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verifica si un código de cupón es válido y está disponible para su uso.
     *
     * @param string $code El código del cupón a verificar.
     * @param int $id El ID del cupón.
     * @return \Illuminate\Http\JsonResponse Retorna una respuesta JSON con los detalles del cupón si es válido, de lo contrario, retorna un mensaje de error.
     */
    public function chekCuponCode(string $code, int $id)
    {
        // Consulta SQL para obtener los detalles del cupón según el código proporcionado
        $query = "SELECT
        td.id,
        td.cliente_id AS clienteId,
        CONCAT_WS(' ', c.nombre1, c.apellido1) AS cliente,
        c.documento AS documento,
        td.nombre,
        td.descuento,
        td.fecha_inicio AS fechaInicio,
        td.fecha_fin AS fechaFin,
        td.precio_id AS precioId,
        tdp.nombre AS precio,
        tdcc.codigo AS codigo,
        td.tipo_id AS tipoId,
        tdt.tipo AS tipo
        FROM tarifa_descuento_cupones td
        LEFT JOIN tarifa_descuento_precios tdp ON tdp.id = td.precio_id
        LEFT JOIN tarifa_descuento_tipos tdt ON tdt.id = td.tipo_id
        LEFT JOIN clients c ON c.id = td.cliente_id
        JOIN tarifa_descuento_cupones_codigos tdcc ON tdcc.cupon_id = td.id
        WHERE td.deleted_at IS NULL AND td.activo = 1
        AND FIND_IN_SET(?, REPLACE(REPLACE(td.habitaciones, '[', ''), ']', '')) > 0
        AND tdcc.codigo = ?
        AND tdcc.activo = 1
        AND tdcc.usado = 0";

        // Ejecutar la consulta SQL con los parámetros proporcionados
        $result = DB::selectOne($query, [
            $id,
            $code,
        ]);

        // Verificar si se encontraron resultados
        if (empty($result)) {
            // Si no se encontraron resultados, devolver un mensaje de error
            return response()->json([
                'message' => 'El Código del Cupón no es válido o no está disponible para su uso.',
            ], 500);
        } else {
            // Si se encontraron resultados, devolver los detalles del cupón
            return response()->json($result, 200);
        }
    }

    /**
     * Actualiza un cupón existente.
     *
     * Esta función actualiza un cupón existente en la base de datos.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP que contiene los datos actualizados del cupón.
     * @param int $id El ID del cupón que se va a actualizar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si el cupón se actualizó correctamente o si se produjo un error.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'fechaInicio' => 'required|string',
            'fechaFin' => 'required|string',
            'nombre' => 'required|string',
            'descuento' => 'required|integer',
            'habitaciones' => 'required|array',
            'tipo' => 'required|integer',
            'precio' => 'required|integer',
            'cantidad' => 'required|integer',
            'activo' => 'required|boolean',
            'user' => 'required|integer',
        ]);

        // Consulta SQL para actualizar el cupón por ID
        $query = 'UPDATE tarifa_descuento_cupones SET
        fecha_inicio = ?,
        fecha_fin = ?,
        cliente_id = ?,
        nombre = ?,
        descuento = ?,
        habitaciones = ?,
        tipo_id = ?,
        precio_id = ?,
        activo = ?,
        user_actualizo_id = ?,
        updated_at = NOW()
        WHERE id = ?';

        $queryGetCountCodes = 'SELECT
        COUNT(*) AS cantidad
        FROM tarifa_descuento_cupones_codigos tdcc
        WHERE tdcc.cupon_id = ? AND tdcc.deleted_at IS NULL';

        $queryInsertCodes = 'INSERT INTO tarifa_descuento_cupones_codigos (
        cupon_id,
        codigo,
        created_at)
        VALUES (?, ?, NOW())';

        DB::beginTransaction();

        try {
            // Ejecutar la actualización del cupón por ID
            DB::update($query, [
                $request->fechaInicio,
                $request->fechaFin,
                $request->cliente,
                $request->nombre,
                $request->descuento,
                json_encode($request->habitaciones),
                $request->tipo,
                $request->precio,
                $request->activo,
                $request->user,
                $id,
            ]);

            $result = DB::selectOne($queryGetCountCodes, [$id]);

            $newCountForAdd = $request->cantidad - $result->cantidad;

            // Insertar codigos asociados al cupón
            for ($i = 0; $i < $newCountForAdd; $i++) {
                DB::insert($queryInsertCodes, [
                    $id,
                    $this->generarCodigoAleatorio(6),
                ]);
            }

            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Cupón actualizado exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar el cupón',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza el estado de los códigos de cupones.
     *
     * @param Request $request La solicitud HTTP entrante.
     * @return JsonResponse La respuesta JSON indicando el resultado de la operación.
     */
    public function updateCodes(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'codigos' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $codigo) {
                        $validator = validator($codigo, [
                            'id' => 'required|integer',
                            'activo' => 'required|boolean',
                        ]);

                        if ($validator->fails()) {
                            $fail('El formato de los códigos es incorrecto. { id:integer, activo:boolean }');
                            break;
                        }
                    }
                }
            ],
        ]);

        // Consulta SQL para actualizar el estado de los códigos de cupones por ID
        $query = 'UPDATE tarifa_descuento_cupones_codigos SET
        activo = ?,
        updated_at = NOW()
        WHERE id = ?';

        // Obtener datos de los códigos de cupones a actualizar
        $codigos = $request->input('codigos');

        DB::beginTransaction();

        try {
            // Actualizar el estado de cada código de cupón en la lista
            foreach ($codigos as $codigo) {
                DB::update($query, [
                    $codigo['activo'],
                    $codigo['id'],
                ]);
            }

            // Confirmar la transacción
            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'message' => 'Guardado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Respuesta de error
            return response()->json([
                'message' => 'Error al guardar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina un cupón por su ID.
     *
     * Esta función marca como eliminado un cupón en la base de datos.
     *
     * @param int $id El ID del cupón que se va a eliminar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON indicando si el cupón se eliminó correctamente o si se produjo un error.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar el cupón como eliminado por ID
        $query = 'UPDATE tarifa_descuento_cupones SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar el cupón como eliminado
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Cupón eliminado exitosamente',
                ]);
            } else {
                // Devolver un mensaje de error si la eliminación no fue exitosa
                return response()->json([
                    'message' => 'Error al eliminar el cupón',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar el cupón',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Genera un código aleatorio con la longitud especificada.
     *
     * @param int $longitud La longitud del código a generar.
     * @return string El código aleatorio generado.
     */
    function generarCodigoAleatorio($longitud)
    {
        // Definir los caracteres posibles para el código
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        // Inicializar la variable para almacenar el código
        $codigo = '';

        // Calcular la longitud de la cadena de caracteres
        $longitud_caracteres = strlen($caracteres);

        // Iterar para generar cada carácter del código
        for ($i = 0; $i < $longitud; $i++) {
            // Generar un índice aleatorio dentro del rango de caracteres
            $indice = rand(0, $longitud_caracteres - 1);

            // Concatenar el carácter correspondiente al índice generado
            $codigo .= $caracteres[$indice];
        }

        // Devolver el código generado
        return $codigo;
    }
}
