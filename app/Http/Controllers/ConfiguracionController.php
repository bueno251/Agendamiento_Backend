<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\configuracion;

class ConfiguracionController extends Controller
{
    public function read()
    {
        $query = 'SELECT id,
        usuario_reserva,
        correo,
        telefono,
        nombre_empresa,
        nit,
        ciudad,
        departamento,
        pais,
        created_at
        FROM configuracions
        WHERE deleted_at IS NULL';

        $queryTipoPagos = 'SELECT
        tp.id AS id,
        tp.tipo AS tipo,
        sp.estado AS estado
        FROM reserva_tipo_pagos tp
        LEFT JOIN configuracion_pagos sp ON sp.reserva_tipo_pago_id = tp.id
        WHERE tp.deleted_at IS NULL';


        $configuration = DB::select($query);
        $pagos = DB::select($queryTipoPagos);

        $json = array(
            "settings" => $configuration[0],
            "pagos" => $pagos,
        );

        return response($json, 200);
    }

    public function pagos(Request $request)
    {
        $request->validate([
            'configuracionId' => 'required',
            'pagos' => 'required',
        ]);

        $pagos = $request->input('pagos');

        $query = 'INSERT INTO configuracion_pagos
        (configuracion_id,
        reserva_tipo_pago_id,
        estado,
        created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE estado = ?, updated_at = NOW()';

        foreach ($pagos as $tipo) {
            DB::insert($query, [
                $request->configuracionId,
                $tipo['id'],
                $tipo['estado'],
                $tipo['estado'],
            ]);
        }

        return response('Tipo de pagos guardados', 200);
    }
}
