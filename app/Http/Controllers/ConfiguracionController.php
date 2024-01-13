<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class ConfiguracionController extends Controller
{
    public function read()
    {
        $queryConfig = 'SELECT
        id,
        usuario_reserva,
        id_empresa AS empresa
        FROM configuracions';

        $queryTipoPagos = 'SELECT
        tp.id AS id,
        tp.tipo AS tipo,
        sp.estado AS estado
        FROM reserva_tipo_pagos tp
        LEFT JOIN configuracion_pagos sp ON sp.reserva_tipo_pago_id = tp.id
        WHERE tp.deleted_at IS NULL';

        $pagos = DB::select($queryTipoPagos);
        $configuration = DB::select($queryConfig);

        $configuration[0]->pagos = $pagos;
        $configuration[0]->usuario_reserva = $configuration[0]->usuario_reserva ? true : false;
        $configuration[0]->empresa = $configuration[0]->empresa ? $this->getEmpresa($configuration[0]->empresa) : null;

        return response($configuration, 200);
    }

    public function pagos(Request $request)
    {
        $request->validate([
            'configuracionId' => 'required|integer',
            'pagos' => 'required',
        ]);

        $pagos = $request->input('pagos');

        $query = 'INSERT INTO configuracion_pagos (
        configuracion_id,
        reserva_tipo_pago_id,
        estado,
        created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE estado = VALUES(estado), updated_at = NOW()';

        try {
            foreach ($pagos as $tipo) {
                DB::insert($query, [
                    $request->configuracionId,
                    $tipo['id'],
                    $tipo['estado'],
                ]);
            }

            return response()->json([
                'message' => 'Tipo de pagos guardados',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reservar(Request $request)
    {
        $request->validate([
            'configuracionId' => 'required|integer',
            'reservar' => 'required',
        ]);

        $query = 'UPDATE configuracions SET 
        usuario_reserva = ?,
        updated_at = now()
        WHERE id = ?';

        $reservar = DB::update($query, [
            $request->reservar,
            $request->configuracionId,
        ]);

        if ($reservar) {
            return response()->json([
                'message' => 'Cambios Guardados',
            ]);
        } else {
            return response()->json([
                'message' => 'Error al guardar',
            ], 500);
        }
    }

    public function empresa(Request $request)
    {
        $request->validate([
            'configuracionId' => 'required|integer',
            'nombre' => 'required|string',
            'tipoDocumento' => 'required|integer',
            'identificacion' => 'required',
            'dv' => 'required',
            'registro' => 'required',
            'pais' => 'required|string',
            'departamento' => 'required|string',
            'municipio' => 'required|string',
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

        $query = 'INSERT INTO empresa (
        nombre,
        id_tipo_documento,
        identificacion,
        dv,
        registro_mercantil,
        pais,
        departamento,
        municipio,
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
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

        try {
            DB::insert($query, [
                $request->nombre,
                $request->tipoDocumento,
                $request->identificacion,
                $request->dv,
                $request->registro,
                $request->pais,
                $request->departamento,
                $request->municipio,
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

            $empresaId = DB::getPdo()->lastInsertId();

            $query = 'UPDATE configuracions SET 
            id_empresa = ?,
            updated_at = now()
            WHERE id = ?';

            $gardar = DB::update($query, [
                $empresaId,
                $request->configuracionId,
            ]);

            if ($gardar) {
                return response()->json([
                    'message' => 'Empresa guardada',
                ]);
            } else {
                return response()->json([
                    'message' => 'Error al guardar',
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al guardar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function empresaTypes()
    {
        $queryTipoDocumento = 'SELECT
        id,
        tipo,
        created_at
        FROM cliente_tipo_documento
        WHERE deleted_at IS NULL';

        $queryTipoPersona = 'SELECT
        id,
        tipo,
        created_at
        FROM cliente_tipo_persona
        WHERE deleted_at IS NULL';

        $queryTipoRegimen = 'SELECT
        id,
        tipo,
        created_at
        FROM cliente_tipo_regimen
        WHERE deleted_at IS NULL';

        $queryTipoResponsabilidad = 'SELECT
        id,
        tipo,
        created_at
        FROM cliente_tipo_obligacion
        WHERE deleted_at IS NULL';

        $queryTipoOperacion = 'SELECT
        id,
        tipo,
        created_at
        FROM empresa_tipo_operacion
        WHERE deleted_at IS NULL';

        $queryTipoEntorno = 'SELECT
        id,
        tipo,
        created_at
        FROM empresa_tipo_entorno
        WHERE deleted_at IS NULL';

        $documentos = DB::select($queryTipoDocumento);
        $organizaciones = DB::select($queryTipoPersona);
        $responsabilidades = DB::select($queryTipoResponsabilidad);
        $regimenes = DB::select($queryTipoRegimen);
        $operaciones = DB::select($queryTipoOperacion);
        $entornos = DB::select($queryTipoEntorno);

        return response()->json([
            'documentos' => $documentos,
            'organizaciones' => $organizaciones,
            'responsabilidades' => $responsabilidades,
            'regimenes' => $regimenes,
            'operaciones' => $operaciones,
            'entornos' => $entornos,
        ]);
    }

    public function getEmpresa($id)
    {
        $query = 'SELECT
        e.id AS id,
        e.id_tipo_documento AS idDocumento,
        ctd.tipo AS documento,
        e.identificacion AS identificacion,
        e.nombre AS nombre,
        e.dv AS dv,
        e.registro_mercantil AS registro,
        e.pais AS pais,
        e.departamento AS departamento,
        e.municipio AS municipio,
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
        WHERE e.id = ? && e.deleted_at IS NULL';

        $empresas = DB::select($query, [
            $id
        ]);

        if (count($empresas) > 0) {
            return $empresas[0];
        } else {
            return null;
        }
    }

    public function getPagos()
    {
        $query = 'SELECT
        tp.id AS id,
        tp.tipo AS tipo,
        sp.estado AS estado
        FROM reserva_tipo_pagos tp
        LEFT JOIN configuracion_pagos sp ON sp.reserva_tipo_pago_id = tp.id
        WHERE sp.estado = 1
        AND tp.deleted_at IS NULL';

        $pagos = DB::select($query);

        return response($pagos, 200);
    }
}
