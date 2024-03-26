<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Crear Cliente
     *
     * Este método se encarga de crear un nuevo cliente en la base de datos.
     * La información del cliente se recibe a través de una solicitud HTTP, se valida y se realiza la inserción de datos en la tabla correspondiente.
     * Además, se verifica si el documento ya está registrado antes de la inserción.
     *
     * @param Request $request Datos de entrada que incluyen información sobre el cliente.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function create(Request $request)
    {
        $request->validate([
            'nombre1' => 'required|string',
            'apellido1' => 'required|string',
            'tipoDocumento' => 'required|integer',
            'documento' => 'required|integer',
            'direccion' => 'required|string',
            'pais' => 'required|integer',
            'departamento' => 'required|integer',
            'ciudad' => 'required|integer',
            'telefono' => 'required|string',
            'tipoPersona' => 'required|integer',
            'tipoObligacion' => 'required|integer',
            'tipoRegimen' => 'required|integer',
        ]);

        // Verificar si el documento ya está registrado
        if ($this->documentoExistente($request->documento)) {
            return response()->json([
                'message' => 'Documento ya registrado',
            ], 500);
        }

        // Consulta SQL para insertar el cliente
        $query = 'INSERT INTO clients
        (nombre1,
        nombre2,
        apellido1,
        apellido2,
        tipo_documento_id,
        documento,
        direccion,
        pais_id,
        departamento_id,
        ciudad_id,
        telefono,
        telefono_alt,
        tipo_persona_id,
        tipo_obligacion_id,
        tipo_regimen_id,
        observacion,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        try {
            // Ejecutar la inserción del cliente
            DB::insert($query, [
                $request->nombre1,
                $request->nombre2,
                $request->apellido1,
                $request->apellido2,
                $request->tipoDocumento,
                $request->documento,
                $request->direccion,
                $request->pais,
                $request->departamento,
                $request->ciudad,
                $request->telefono,
                $request->telefonoAlt,
                $request->tipoPersona,
                $request->tipoObligacion,
                $request->tipoRegimen,
                $request->observacion,
            ]);

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Cliente creado exitosamente',
            ]);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al crear el cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener Lista de Clientes
     *
     * Este método se encarga de obtener la lista de clientes desde la base de datos.
     * Los clientes se seleccionan de la tabla correspondiente, y la respuesta se devuelve en formato JSON.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON que contiene la lista de clientes o un mensaje de error en caso de fallo.
     */
    public function read()
    {
        $query = 'SELECT
        c.id AS id,
        c.nombre1 AS nombre1,
        c.nombre2 AS nombre2,
        c.apellido1 AS apellido1,
        c.apellido2 AS apellido2,
        CONCAT_WS(" ", c.nombre1, c.nombre2, c.apellido1, c.apellido2) AS fullname,
        c.tipo_documento_id AS tipo_documento_id,
        c.documento AS documento,
        c.direccion AS direccion,
        c.pais_id AS paisId,
        c.departamento_id AS departamentoId,
        c.ciudad_id AS ciudadId,
        c.telefono AS telefono,
        c.telefono_alt AS telefono_alt,
        c.tipo_persona_id AS tipo_persona_id,
        c.tipo_obligacion_id AS tipo_obligacion_id,
        c.tipo_regimen_id AS tipo_regimen_id,
        c.observacion AS observacion,
        c.created_at AS created_at
        FROM clients c
        WHERE c.deleted_at IS NULL
        ORDER BY c.created_at DESC';

        try {
            // Ejecutar la consulta para obtener la lista de clientes
            $clients = DB::select($query);

            // Retornar la lista de clientes en formato JSON
            return response()->json($clients, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener la lista de clientes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener el Cliente por ID
     *
     * Este método se encarga de obtener el cliente específico mediante su ID desde la base de datos.
     * Los detalles incluyen información sobre el cliente y se devuelven en formato JSON.
     *
     * @param int $id ID del cliente que se desea obtener.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON que contiene el cliente o un mensaje de error en caso de fallo.
     */
    public function find($id)
    {
        $query = 'SELECT 
        c.id AS id,
        c.nombre1 AS nombre1,
        c.nombre2 AS nombre2,
        c.apellido1 AS apellido1,
        c.apellido2 AS apellido2,
        CONCAT_WS(" ", c.nombre1, c.nombre2, c.apellido1, c.apellido2) AS fullname,
        c.tipo_documento_id AS tipo_documento_id,
        td.tipo AS tipo_documento,
        c.documento AS documento,
        c.direccion AS direccion,
        c.pais_id AS paisId,
        c.departamento_id AS departamentoId,
        c.ciudad_id AS ciudadId,
        c.telefono AS telefono,
        c.telefono_alt AS telefono_alt,
        c.tipo_persona_id AS tipo_persona_id,
        tp.tipo AS tipo_persona,
        c.tipo_obligacion_id AS tipo_obligacion_id,
        cto.tipo AS tipo_obligacion,
        c.tipo_regimen_id AS tipo_regimen_id,
        tr.tipo AS tipo_regimen,
        c.observacion AS observacion,
        c.created_at AS created_at
        FROM clients c
        LEFT JOIN cliente_tipo_documento td ON c.tipo_documento_id = td.id
        LEFT JOIN cliente_tipo_persona tp ON c.tipo_persona_id = tp.id
        LEFT JOIN cliente_tipo_obligacion cto ON c.tipo_obligacion_id = cto.id
        LEFT JOIN cliente_tipo_regimen tr ON c.tipo_regimen_id = tr.id
        WHERE c.id = ? AND c.deleted_at IS NULL';

        try {
            // Ejecutar la consulta para obtener el cliente por ID
            $clientDetails = DB::select($query, [$id]);

            // Retornar el cliente en formato JSON
            return response()->json($clientDetails, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al obtener los el cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar Cliente por Documento
     *
     * Este método se encarga de buscar un cliente mediante su número de documento en la base de datos.
     * Retorna los detalles del cliente en formato JSON.
     *
     * @param string $doc Número de documento del cliente.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON que contiene los detalles del cliente o un mensaje de error en caso de fallo.
     */
    public function findDoc($doc)
    {
        $query = 'SELECT
        id,
        nombre1,
        nombre2,
        apellido1,
        apellido2,
        CONCAT_WS(" ", nombre1, nombre2, apellido1, apellido2) as fullname,
        tipo_documento_id,
        documento,
        direccion,
        pais_id AS paisId,
        departamento_id AS departamentoId,
        ciudad_id AS ciudadId,
        telefono,
        telefono_alt,
        tipo_persona_id,
        tipo_obligacion_id,
        tipo_regimen_id,
        observacion,
        created_at
        FROM clients
        WHERE documento = ? AND deleted_at IS NULL';

        try {
            // Ejecutar la consulta para buscar el cliente por número de documento
            $cliente = DB::selectOne($query, [$doc]);

            // Retornar los detalles del cliente en formato JSON
            return response()->json($cliente, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al buscar el cliente por documento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar Documento Existente
     *
     * Este método se encarga de verificar si un documento ya está registrado en la base de datos.
     *
     * @param int $documento Número de documento a verificar.
     * @return bool Devuelve true si el documento existe, false si no.
     */
    public function documentoExistente($documento)
    {
        // Consulta SQL para verificar si el documento existe
        $query = 'SELECT id FROM clients WHERE documento = ? AND deleted_at IS NULL';

        // Obtener resultados de la consulta
        $clients = DB::select($query, [$documento]);

        // Devolver true si hay al menos un resultado, indicando que el documento ya existe
        return count($clients) > 0;
    }

    /**
     * Actualizar Cliente
     *
     * Este método se encarga de actualizar la información de un cliente en la base de datos.
     * La información del cliente se recibe a través de una solicitud HTTP, se valida y se realiza la actualización de datos en la tabla correspondiente.
     *
     * @param Request $request Datos de entrada que incluyen la información actualizada del cliente.
     * @param int $id ID del cliente que se actualizará.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre1' => 'required|string',
            'apellido1' => 'required|string',
            'tipoDocumento' => 'required|integer',
            'documento' => 'required|integer',
            'direccion' => 'required|string',
            'pais' => 'required|integer',
            'departamento' => 'required|integer',
            'ciudad' => 'required|integer',
            'telefono' => 'required|string',
            'tipoPersona' => 'required|integer',
            'tipoObligacion' => 'required|integer',
            'tipoRegimen' => 'required|integer',
        ]);

        // Consulta SQL para actualizar el cliente
        $query = 'UPDATE clients SET 
        nombre1 = ?,
        nombre2 = ?,
        apellido1 = ?,
        apellido2 = ?,
        tipo_documento_id = ?,
        documento = ?,
        direccion = ?,
        pais_id = ?,
        departamento_id = ?,
        ciudad_id = ?,
        telefono = ?,
        telefono_alt = ?,
        tipo_persona_id = ?,
        tipo_obligacion_id = ?,
        tipo_regimen_id = ?,
        observacion = ?,
        updated_at = NOW()
        WHERE id = ?';

        try {
            // Ejecutar la actualización de los detalles del cliente
            $result = DB::update($query, [
                $request->nombre1,
                $request->nombre2,
                $request->apellido1,
                $request->apellido2,
                $request->tipoDocumento,
                $request->documento,
                $request->direccion,
                $request->pais,
                $request->departamento,
                $request->ciudad,
                $request->telefono,
                $request->telefonoAlt,
                $request->tipoPersona,
                $request->tipoObligacion,
                $request->tipoRegimen,
                $request->observacion,
                $id
            ]);

            // Verificar si la actualización fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Cliente actualizado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al actualizar el cliente',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al actualizar el cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar Cliente
     *
     * Este método se encarga de marcar un cliente como eliminado en la base de datos.
     *
     * @param int $id ID del cliente que se eliminará.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function delete($id)
    {
        // Consulta SQL para marcar al cliente como eliminado
        $query = 'UPDATE clients SET deleted_at = NOW() WHERE id = ?';

        try {
            // Ejecutar la actualización para marcar al cliente como eliminado
            $result = DB::update($query, [$id]);

            // Verificar si la eliminación fue exitosa
            if ($result) {
                return response()->json([
                    'message' => 'Cliente eliminado exitosamente',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al eliminar el cliente',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles en caso de fallo
            return response()->json([
                'message' => 'Error al eliminar el cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
