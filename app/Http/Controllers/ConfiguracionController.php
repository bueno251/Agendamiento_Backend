<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
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
        FROM tipo_pagos tp
        LEFT JOIN configuracion_pagos sp ON sp.tipo_pago_id = tp.id
        WHERE tp.deleted_at IS NULL';


        $configuration = DB::select($query);
        $pagos = DB::select($queryTipoPagos);

        $json = array(
            "settings" => $configuration,
            "pagos" => $pagos,
        );

        return response($json, 200);
    }
}
