<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfigFormReservaController extends Controller
{
    public function saveConfig(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'paisResidencia' => 'required|integer',
            'departamentoResidencia' => 'required|integer',
            'ciudadResidencia' => 'required|integer',
            'paisProcedencia' => 'required|integer',
            'departamentoProcedencia' => 'required|integer',
            'ciudadProcedencia' => 'required|integer',
            'tipoDocumento' => 'required|integer',
        ]);

        // Consulta SQL para verificar si ya existe una configuración por defecto para la configuración principal
        $existingConfigQuery = 'SELECT id FROM config_defecto_form_reserva WHERE deleted_at IS NULL';

        // Consulta SQL para insertar la configuración por defecto
        $insertQuery = 'INSERT INTO config_defecto_form_reserva (
        pais_residencia_id,
        departamento_residencia_id,
        ciudad_residencia_id,
        pais_procedencia_id,
        departamento_procedencia_id,
        ciudad_procedencia_id,
        tipo_documento_id,
        created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())';

        // Consulta SQL para actualizar la configuración por defecto
        $updateConfig = 'UPDATE config_defecto_form_reserva SET 
        pais_residencia_id = ?,
        departamento_residencia_id = ?,
        ciudad_residencia_id = ?,
        pais_procedencia_id = ?,
        departamento_procedencia_id = ?,
        ciudad_procedencia_id = ?,
        tipo_documento_id = ?,
        updated_at = NOW()';

        DB::beginTransaction();

        try {
            // Verificar si ya existe una configuración por defecto
            $existingConfig = DB::selectOne($existingConfigQuery);

            if ($existingConfig) {

                DB::update($updateConfig, [
                    $request->paisResidencia,
                    $request->departamentoResidencia,
                    $request->ciudadResidencia,
                    $request->paisProcedencia,
                    $request->departamentoProcedencia,
                    $request->ciudadProcedencia,
                    $request->tipoDocumento,
                ]);
            } else {
                // Si no existe, insertar una nueva configuración
                DB::insert($insertQuery, [
                    $request->paisResidencia,
                    $request->departamentoResidencia,
                    $request->ciudadResidencia,
                    $request->paisProcedencia,
                    $request->departamentoProcedencia,
                    $request->ciudadProcedencia,
                    $request->tipoDocumento,
                ]);
            }

            // Commit de la transacción
            DB::commit();

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Configuración del formulario de reserva guardada con éxito',
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

    public function getConfig()
    {
        $query = 'SELECT
        id,
        pais_residencia_id AS paisResidencia,
        departamento_residencia_id AS departamentoResidencia,
        ciudad_residencia_id AS ciudadResidencia,
        pais_procedencia_id AS paisProcedencia,
        departamento_procedencia_id AS departamentoProcedencia,
        ciudad_procedencia_id AS ciudadProcedencia,
        tipo_documento_id AS tipoDocumento
        FROM config_defecto_form_reserva
        WHERE deleted_at IS NULL';

        try {
            // Ejecutar la consulta
            $configuration = DB::selectOne($query);

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
}
