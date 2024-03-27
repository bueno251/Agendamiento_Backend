<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    /**
     * Leer Configuración
     *
     * Este método se encarga de leer la configuración actual del sistema. Realiza consultas a la base de datos para obtener información sobre la configuración general, los metodos de pagos y la empresa asociada.
     *
     * @return \Illuminate\Http\Response Respuesta JSON con la configuración actual, incluyendo metodos de pagos y detalles de la empresa, o un mensaje de error en caso de fallo.
     */
    public function read()
    {
        // Consulta SQL para obtener la configuración general
        $queryConfig = 'SELECT
        id,
        usuario_reserva AS usuarioReserva,
        calendario_inhabilitado AS calendarioInhabilitado,
        correo_obligatorio AS correoObligatorio,
        porcentaje_separacion AS porcentajeSeparacion,
        tarifas_generales AS tarifasGenerales,
        edad_tarifa_niños AS edadTarifaNiños,
        id_empresa AS empresa,
        (
            SELECT
            JSON_ARRAYAGG(JSON_OBJECT("id", rtp.id, "nombre", rtp.nombre, "estado", cp.estado))
            FROM reserva_metodo_pagos rtp
            LEFT JOIN configuracion_pagos cp ON cp.reserva_metodo_pago_id = rtp.id
            WHERE rtp.deleted_at IS NULL
        ) AS pagos
        FROM configuracions';

        try {
            // Ejecutar consulta
            $configuration = DB::selectOne($queryConfig);

            // Decodificar metodos de pagos de la configuración
            $configuration->pagos = json_decode($configuration->pagos);

            // Convertir el campo 'usuario_reserva' a un formato booleano
            $configuration->usuarioReserva = (bool) $configuration->usuarioReserva;
            $configuration->calendarioInhabilitado = (bool) $configuration->calendarioInhabilitado;
            $configuration->correoObligatorio = (bool) $configuration->correoObligatorio;
            $configuration->tarifasGenerales = (bool) $configuration->tarifasGenerales;

            // Obtener detalles de la empresa si está asociada
            $configuration->empresa = $configuration->empresa ? $this->getEmpresa($configuration->empresa) : null;

            // Retornar respuesta exitosa
            return response()->json($configuration, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al traer la configuracion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Configurar metodos de Pagos
     *
     * Este método se encarga de configurar los metodos de pagos para una empresa en el sistema. La información de los metodos de pagos se recibe a través de una solicitud HTTP, se valida y se realiza la inserción o actualización de datos en la tabla correspondiente de la base de datos.
     * Se utiliza la cláusula ON DUPLICATE KEY UPDATE para manejar conflictos en caso de duplicados y se maneja cualquier error que pueda ocurrir durante el proceso.
     *
     * @param Request $request Datos de entrada que incluyen información como 'configuracionId' (integer, obligatorio), 'pagos' (array, obligatorio) que contiene información sobre los metodos de pagos a configurar.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function pagos(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'configuracionId' => 'required|integer',
            'metodosPago' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($value as $pago) {
                        $validate = validator($pago, [
                            'id' => 'required|integer',
                            'estado' => 'required|integer',
                        ]);

                        if ($validate->fails()) {
                            $fail('el formato de los metodosPago es incorrecto: { id:integer, estado:integer}');
                            break;
                        }
                    }
                }
            ],
        ]);

        // Obtener la información de metodos pago desde la solicitud
        $metodosPago = $request->input('metodosPago');

        // Consulta SQL para insertar o actualizar metodos de pagos
        $query = 'INSERT INTO configuracion_pagos (
        configuracion_id,
        reserva_metodo_pago_id,
        estado,
        created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE estado = VALUES(estado), updated_at = NOW()';

        // Iniciar transacción
        DB::beginTransaction();

        try {
            // Iterar sobre cada metodo de pago y realizar la inserción o actualización
            foreach ($metodosPago as $metodoPago) {
                DB::insert($query, [
                    $request->configuracionId,
                    $metodoPago['id'],
                    $metodoPago['estado'],
                ]);
            }

            // Commit de la transacción
            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Metodos de pagos guardados con éxito',
            ]);
        } catch (\Exception $e) {
            // Rollback en caso de error
            DB::rollBack();

            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al guardar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Configurar Método de Pago
     *
     * Este método se encarga de configurar un método de pago para el sistema de reservas. La información del método de pago se recibe a través de una solicitud HTTP, se valida y se realiza la inserción en la tabla correspondiente de la base de datos.
     * Se utiliza una transacción para garantizar la integridad de los datos y se manejan posibles errores durante el proceso.
     *
     * @param  \Illuminate\Http\Request  $request Datos de entrada que incluyen información como 'nombre' (string, obligatorio) que representa el nombre del método de pago.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function metodoPago(Request $request)
    {
        // Validar la solicitud del cliente
        $request->validate([
            'nombre' => 'required|string',
        ]);

        // Consulta de inserción
        $insertQuery = 'INSERT INTO reserva_metodo_pagos (
        nombre,
        created_at)
        VALUES (?, NOW())';

        // Iniciar transacción
        DB::beginTransaction();

        try {
            // Ejecutar la inserción del método de pago
            DB::insert($insertQuery, [
                $request->nombre,
            ]);

            // Confirmar la transacción
            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Método de pago guardado con éxito',
            ]);
        } catch (\Exception $e) {
            // Rollback en caso de error
            DB::rollBack();

            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al guardar el método de pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reservar Configuración
     *
     * Este método se encarga de actualizar la configuración de reserva para un usuario en el sistema. La información de reserva se recibe a través de una solicitud HTTP, se valida y se realiza la actualización de datos en la tabla correspondiente de la base de datos.
     * Se maneja cualquier error que pueda ocurrir durante el proceso.
     *
     * @param Request $request Datos de entrada que incluyen información como 'configuracionId' (integer, obligatorio), 'reservar' (integer, obligatorio).
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function reservar(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'configuracionId' => 'required|integer',
            'reservar' => 'required|boolean',
            'calendario' => 'required|boolean',
            'correo' => 'required|boolean',
            'tarifasGenerales' => 'required|boolean',
            'edadTarifaNiños' => 'required|integer',
            'porcentaje' => 'required|integer',
        ]);

        // Consulta SQL para actualizar la configuración de reserva
        $updateQuery = 'UPDATE configuracions SET 
        usuario_reserva = ?,
        calendario_inhabilitado = ?,
        correo_obligatorio = ?,
        porcentaje_separacion = ?,
        tarifas_generales = ?,
        edad_tarifa_niños = ?,
        updated_at = NOW()
        WHERE id = ?';

        try {
            // Ejecutar la actualización de la configuración de reserva
            $reservar = DB::update($updateQuery, [
                $request->reservar,
                $request->calendario,
                $request->correo,
                $request->porcentaje,
                $request->tarifasGenerales,
                $request->edadTarifaNiños,
                $request->configuracionId,
            ]);

            if ($reservar) {
                // Retornar respuesta de éxito
                return response()->json([
                    'message' => 'Cambios Guardados',
                ]);
            } else {
                // Retornar respuesta de error
                return response()->json([
                    'message' => 'Error al guardar la configuración de reserva',
                ], 500);
            }
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al guardar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear Empresa
     *
     * Este método se encarga de crear una nueva empresa en el sistema. La información de la empresa se recibe a través de una solicitud HTTP, se valida y se realiza la inserción y actualización de datos en las tablas correspondientes de la base de datos.
     * Se utiliza una transacción para garantizar la consistencia de los datos, y se maneja cualquier error que pueda ocurrir durante el proceso.
     *
     * @param Request $request Datos de entrada que incluyen información como 'configuracionId' (integer, obligatorio), 'nombre' (string, obligatorio), 'tipoDocumento' (integer, obligatorio), 'identificacion' (string, obligatorio), 'dv' (string, obligatorio), 'registro' (string, obligatorio), 'pais' (string, obligatorio), 'departamento' (string, obligatorio), 'ciudad' (string, obligatorio), 'direccion' (string, obligatorio), 'correo' (string, obligatorio), 'telefono' (string, obligatorio), 'lenguaje' (string, obligatorio), 'impuesto' (string, obligatorio), 'tipoOperacion' (integer, obligatorio), 'tipoEntorno' (integer, obligatorio), 'tipoOrganizacion' (integer, obligatorio), 'tipoResponsabilidad' (integer, obligatorio), 'tipoRegimen' (integer, obligatorio).
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function empresa(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'configuracionId' => 'required|integer',
            'codigoRNT' => 'required|string',
            'codigoCIIU' => 'required|string',
            'nombre' => 'required|string',
            'tipoDocumento' => 'required|integer',
            'identificacion' => 'required',
            'dv' => 'required',
            'registro' => 'required',
            'pais' => 'required|integer',
            'departamento' => 'required|integer',
            'ciudad' => 'required|integer',
            'direccion' => 'required|string',
            'correo' => 'required|email',
            'telefono' => 'required',
            'lenguaje' => 'required|string',
            'impuesto' => 'required|string',
            'tipoOperacion' => 'required|integer',
            'tipoEntorno' => 'required|integer',
            'tipoOrganizacion' => 'required|integer',
            'tipoResponsabilidad' => 'required|integer',
            'tipoRegimen' => 'required|integer',
        ]);

        // Consulta SQL para insertar la empresa
        $insertQuery = 'INSERT INTO empresa (
        codigo_rnt,
        codigo_ciiu,
        nombre,
        id_tipo_documento,
        identificacion,
        dv,
        registro_mercantil,
        pais_id,
        departamento_id,
        ciudad_id,
        direccion,
        correo,
        telefono,
        lenguaje,
        impuesto,
        id_operacion,
        id_entorno,
        id_organizacion,
        id_responsabilidad,
        id_regimen,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        // Iniciar transacción
        DB::beginTransaction();

        try {
            // Ejecutar la inserción de la empresa
            DB::insert($insertQuery, [
                $request->codigoRNT,
                $request->codigoCIIU,
                $request->nombre,
                $request->tipoDocumento,
                $request->identificacion,
                $request->dv,
                $request->registro,
                $request->pais,
                $request->departamento,
                $request->ciudad,
                $request->direccion,
                $request->correo,
                $request->telefono,
                $request->lenguaje,
                $request->impuesto,
                $request->tipoOperacion,
                $request->tipoEntorno,
                $request->tipoOrganizacion,
                $request->tipoResponsabilidad,
                $request->tipoRegimen,
            ]);

            // Obtener el ID de la empresa recién creada
            $empresaId = DB::getPdo()->lastInsertId();

            // Consulta SQL para actualizar la configuración principal
            $updateQuery = 'UPDATE configuracions SET 
            id_empresa = ?,
            updated_at = NOW()
            WHERE id = ?';

            // Ejecutar la actualización de la configuración principal
            DB::update($updateQuery, [
                $empresaId,
                $request->configuracionId,
            ]);

            // Commit de la transacción
            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Empresa guardada con éxito',
            ]);
        } catch (\Exception $e) {
            // Rollback en caso de error
            DB::rollBack();

            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al guardar la configuración por defecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Configuración por Defecto
     *
     * Este método se encarga de establecer o actualizar la configuración por defecto para los datos del cliente en el sistema.
     * La información de configuración se recibe a través de una solicitud HTTP, se valida y se realiza la inserción o actualización de datos en las tablas correspondientes de la base de datos.
     * Se utiliza una transacción para garantizar la consistencia de los datos, y se maneja cualquier error que pueda ocurrir durante el proceso.
     *
     * @param Request $request Datos de entrada que incluyen información como 'configuracionId' (integer, obligatorio), 'pais' (string, obligatorio), 'departamento' (string, obligatorio), 'ciudad' (string, obligatorio), 'tipoDocumento' (integer, obligatorio), 'tipoPersona' (integer, obligatorio), 'tipoResponsabilidad' (integer, obligatorio), 'tipoRegimen' (integer, obligatorio).
     * @return \Illuminate\Http\JsonResponse Respuesta JSON indicando el éxito o un mensaje de error en caso de fallo, con detalles sobre el error.
     */
    public function defaultConfig(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'configuracionId' => 'required|integer',
            'pais' => 'required|integer',
            'departamento' => 'required|integer',
            'ciudad' => 'required|integer',
            'priceInDolar' => 'required|boolean',
            'dolarPriceAuto' => 'required|boolean',
            'dolarPrice' => 'required|numeric',
            'divisa' => 'required|integer',
            'tipoDocumento' => 'required|integer',
            'tipoPersona' => 'required|integer',
            'tipoResponsabilidad' => 'required|integer',
            'tipoRegimen' => 'required|integer',
        ]);

        // Consulta SQL para verificar si ya existe una configuración por defecto para la configuración principal
        $existingConfigQuery = 'SELECT id FROM configuracion_defecto WHERE deleted_at IS NULL';

        // Consulta SQL para insertar la configuración por defecto
        $insertQuery = 'INSERT INTO configuracion_defecto (
        pais_id,
        departamento_id,
        ciudad_id,
        price_in_dolar,
        dolar_price_auto,
        dolar_price,
        divisa_id,
        tipo_documento_id,
        tipo_persona_id,
        tipo_obligacion_id,
        tipo_regimen_id,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        // Consulta SQL para actualizar la configuración por defecto
        $updateConfig = 'UPDATE configuracion_defecto SET 
        pais_id = ?,
        departamento_id = ?,
        ciudad_id = ?,
        price_in_dolar = ?,
        dolar_price_auto = ?,
        dolar_price = ?,
        divisa_id = ?,
        tipo_documento_id = ?,
        tipo_persona_id = ?,
        tipo_obligacion_id = ?,
        tipo_regimen_id = ?,
        updated_at = NOW()';

        // Consulta SQL para actualizar la configuración principal
        $updateQuery = 'UPDATE configuracions SET 
        id_config = ?,
        updated_at = NOW()
        WHERE id = ?';

        DB::beginTransaction();

        try {
            // Verificar si ya existe una configuración por defecto
            $existingConfig = DB::selectOne($existingConfigQuery);

            if ($existingConfig) {
                // Si ya existe, actualizar la configuración existente
                $configId = $existingConfig->id;

                DB::update($updateConfig, [
                    $request->pais,
                    $request->departamento,
                    $request->ciudad,
                    $request->priceInDolar,
                    $request->dolarPriceAuto,
                    $request->dolarPrice,
                    $request->divisa,
                    $request->tipoDocumento,
                    $request->tipoPersona,
                    $request->tipoResponsabilidad,
                    $request->tipoRegimen,
                ]);
            } else {
                // Si no existe, insertar una nueva configuración
                DB::insert($insertQuery, [
                    $request->pais,
                    $request->departamento,
                    $request->ciudad,
                    $request->priceInDolar,
                    $request->dolarPriceAuto,
                    $request->dolarPrice,
                    $request->divisa,
                    $request->tipoDocumento,
                    $request->tipoPersona,
                    $request->tipoResponsabilidad,
                    $request->tipoRegimen,
                ]);

                // Obtener el ID de la configuración por defecto recién creada
                $configId = DB::getPdo()->lastInsertId();
            }

            // Ejecutar la actualización de la configuración principal
            DB::update($updateQuery, [
                $configId,
                $request->configuracionId,
            ]);

            // Commit de la transacción
            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Configuración por defecto guardada con éxito',
            ]);
        } catch (\Exception $e) {
            // Rollback en caso de error
            DB::rollBack();

            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al guardar la configuración por defecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener Tipos de Empresas
     *
     * Este método se encarga de obtener diversos tipos de empresas desde la base de datos en una sola consulta.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con los diferentes tipos de empresas, o un mensaje de error en caso de fallo.
     */
    public function empresaTypes()
    {
        $query = 'SELECT id, tipo, created_at, "documentos" AS table_name
        FROM cliente_tipo_documento
        WHERE deleted_at IS NULL
        UNION
        SELECT id, tipo, created_at, "entornos" AS table_name 
        FROM empresa_tipo_entorno 
        WHERE deleted_at IS NULL
        UNION
        SELECT id, tipo, created_at, "operaciones" AS table_name 
        FROM empresa_tipo_operacion 
        WHERE deleted_at IS NULL
        UNION
        SELECT id, tipo, created_at, "organizaciones" AS table_name
        FROM cliente_tipo_persona 
        WHERE deleted_at IS NULL
        UNION
        SELECT id, tipo, created_at, "regimenes" AS table_name
        FROM cliente_tipo_regimen
        WHERE deleted_at IS NULL
        UNION
        SELECT id, tipo, created_at, "responsabilidades" AS table_name
        FROM cliente_tipo_obligacion 
        WHERE deleted_at IS NULL';

        try {
            // Ejecutar la consulta
            $results = DB::select($query);

            $types = [];

            foreach ($results as $result) {
                // Organizar resultados por nombre de la tabla
                $types[$result->table_name][] = (object) [
                    'id' => $result->id,
                    'tipo' => $result->tipo,
                    'created_at' => $result->created_at,
                ];
            }

            // Retornar respuesta exitosa
            return response()->json($types);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al traer los datos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener Detalles de la Empresa por ID
     *
     * Este método se encarga de obtener los detalles de una empresa en base a su ID desde la base de datos.
     *
     * @param int $id ID de la empresa.
     * @return \stdClass|null Detalles de la empresa o null si no se encuentra.
     */
    public function getEmpresa($id)
    {
        $query = 'SELECT
        e.id AS id,
        e.codigo_rnt AS codigoRNT,
        e.codigo_ciiu AS codigoCIIU,
        e.id_tipo_documento AS idDocumento,
        ctd.tipo AS documento,
        e.identificacion AS identificacion,
        e.nombre AS nombre,
        e.dv AS dv,
        e.registro_mercantil AS registro,
        e.pais_id AS paisId,
        e.departamento_id AS departamentoId,
        e.ciudad_id AS ciudadId,
        e.direccion AS direccion,
        e.correo AS correo,
        e.telefono AS telefono,
        e.lenguaje AS lenguaje,
        e.impuesto AS impuesto,
        e.id_operacion AS idOperacion,
        eto.tipo AS operacion,
        e.id_entorno AS idEntorno,
        ete.tipo AS entorno,
        e.id_organizacion AS idOrganizacion,
        ctp.tipo AS organizacion,
        e.id_responsabilidad AS idResponsabilidad,
        cto.tipo AS responsabilidad,
        e.id_regimen AS idRegimen,
        ctr.tipo AS regimen,
        e.created_at AS created_at
        FROM empresa e
        LEFT JOIN empresa_tipo_operacion eto ON e.id_operacion = eto.id
        LEFT JOIN empresa_tipo_entorno ete ON e.id_entorno = ete.id
        LEFT JOIN cliente_tipo_persona ctp ON e.id_organizacion = ctp.id
        LEFT JOIN cliente_tipo_regimen ctr ON e.id_regimen = ctr.id
        LEFT JOIN cliente_tipo_documento ctd ON e.id_tipo_documento = ctd.id
        LEFT JOIN cliente_tipo_obligacion cto ON e.id_responsabilidad = cto.id
        WHERE e.id = ? AND e.deleted_at IS NULL';

        try {
            // Ejecutar la consulta
            $empresa = DB::selectOne($query, [$id]);

            return $empresa;
        } catch (\Exception $e) {
            // Retornar null en caso de error
            return null;
        }
    }

    /**
     * Obtiene la configuración de pagos, incluyendo el estado de cada tipo de pago.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con la configuración de pagos.
     */
    public function getPagos()
    {
        // Consulta SQL para obtener la configuración de pagos y sus estados
        $query = 'SELECT
        tp.id AS id,
        tp.nombre AS nombre,
        tp.requiere_comprobante AS requiereComprobante
        FROM reserva_metodo_pagos tp
        LEFT JOIN configuracion_pagos sp ON sp.reserva_metodo_pago_id = tp.id
        WHERE sp.estado = 1 AND tp.deleted_at IS NULL';

        // Ejecutar la consulta y obtener resultados
        $pagos = DB::select($query);

        foreach ($pagos as $pago) {
            $pago->requiereComprobante = (bool) $pago->requiereComprobante;
        }

        // Devolver la respuesta JSON con la configuración de pagos
        return response()->json($pagos, 200);
    }

    /**
     * Obtener Configuración por Defecto
     *
     * Este método se encarga de obtener la configuración por defecto desde la base de datos.
     *
     * @return \Illuminate\Http\Response Respuesta JSON con la configuración por defecto, o un mensaje de error en caso de fallo.
     */
    public function getDefaultConfig()
    {
        $query = 'SELECT
        cd.id,
        JSON_OBJECT(
            "id", CASE WHEN cd.price_in_dolar = 1 THEN NULL ELSE d.id END,
            "nombre", CASE WHEN cd.price_in_dolar = 1 THEN NULL ELSE d.nombre END,
            "codigo", CASE WHEN cd.price_in_dolar = 1 THEN "USD" ELSE d.codigo END
        ) AS divisa,
        cd.pais_id AS paisId,
        cd.departamento_id AS departamentoId, 
        cd.ciudad_id AS ciudadId, 
        cd.price_in_dolar AS priceInDolar,
        cd.dolar_price_auto AS dolarPriceAuto,
        cd.dolar_price AS dolarPrice,
        cd.tipo_documento_id AS tipo_documento, 
        cd.tipo_persona_id AS tipo_persona, 
        cd.tipo_obligacion_id AS tipo_obligacion, 
        cd.tipo_regimen_id AS tipo_regimen
        FROM configuracion_defecto cd
        JOIN tarifas_divisas d ON cd.divisa_id = d.id
        WHERE cd.deleted_at IS NULL';

        try {
            // Ejecutar la consulta
            $configuration = DB::selectOne($query);

            if ($configuration) {
                $configuration->divisa = json_decode($configuration->divisa);
                $configuration->priceInDolar = (bool) $configuration->priceInDolar;
                $configuration->dolarPriceAuto = (bool) $configuration->dolarPriceAuto;
            }

            // Retornar respuesta exitosa
            return response()->json($configuration, 200);
        } catch (\Exception $e) {
            // Retornar respuesta de error con detalles
            return response()->json([
                'message' => 'Error al traer la configuración por defecto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene la configuración de reserva.
     *
     * Esta función busca en la base de datos la configuración de reserva.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con la configuración de reserva si se encuentra, de lo contrario, devuelve un mensaje de error.
     */
    public function getReservaConfig()
    {
        // Consulta SQL para obtener la configuración de reserva no eliminada
        $query = 'SELECT
        usuario_reserva AS usuarioReserva,
        calendario_inhabilitado AS calendarioInhabilitado,
        correo_obligatorio AS correoObligatorio,
        tarifas_generales AS tarifasGenerales,
        edad_tarifa_niños AS edadTarifaNiños,
        porcentaje_separacion AS porcentajeSeparacion
        FROM configuracions
        WHERE deleted_at IS NULL';

        try {
            // Ejecutar la consulta SQL para obtener la configuración de reserva
            $configuration = DB::selectOne($query);

            // Convertir campos de tipo booleano
            $configuration->usuarioReserva = (bool) $configuration->usuarioReserva;
            $configuration->calendarioInhabilitado = (bool) $configuration->calendarioInhabilitado;
            $configuration->correoObligatorio = (bool) $configuration->correoObligatorio;
            $configuration->tarifasGenerales = (bool) $configuration->tarifasGenerales;

            // Devolver una respuesta JSON con la configuración de reserva si se encuentra
            return response()->json($configuration, 200);
        } catch (\Exception $e) {
            // Si se produce algún error durante la ejecución de la consulta, devolver una respuesta JSON con un mensaje de error y el detalle del error.
            return response()->json([
                'message' => 'Error al traer la configuración',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
