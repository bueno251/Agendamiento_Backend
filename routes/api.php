<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\CaracteristicasController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\clientTipoController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DecoracionController;
use App\Http\Controllers\DesayunoController;
use App\Http\Controllers\ReservasController;
use App\Http\Controllers\RoomBitacoraCambioController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomEstadoController;
use App\Http\Controllers\RoomTipoController;
use App\Http\Controllers\CancelacionTipoController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\DivisasController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\TarifasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Rutas relacionadas con la autenticación.
 */
Route::controller(AuthController::class)->group(function () {
    // Ruta para manejar la solicitud de inicio de sesión
    Route::post('login', 'login');
});

/**
 * Rutas relacionadas con las reservas.
 */
Route::controller(ReservasController::class)->group(function () {
    // Crear una nueva reserva
    Route::post('reserva/create', 'create');

    // Pagar una reserva temporal existente
    Route::post('reserva/pagar', 'pagar');

    // Obtener información de las reservas
    Route::get('reserva/read/{estado?}', 'read');

    // Obtener fechas disponibles para una habitación específica
    Route::get('reserva/room/{id}', 'getDates');

    // Aprobar una reserva existente
    Route::patch('reserva/approve/{id}', 'approve');

    // Rechazar una reserva existente
    Route::patch('reserva/reject/{id}', 'reject');

    //Cancelar una reserva existente
    Route::post('reserva/cancelar/{id}', 'cancelar');
});

/**
 * Rutas relacionadas con clientes.
 */
Route::controller(ClientController::class)->group(function () {
    // Crear un nuevo cliente
    Route::post('client/create', 'create');

    // Obtener información de los clientes
    Route::get('client/read', 'read');

    // Encontrar información de un cliente por ID
    Route::get('client/find/{id}', 'find');

    // Encontrar información de un cliente por número de documento
    Route::get('client/find/document/{doc}', 'findDoc');

    // Actualizar información de un cliente
    Route::patch('client/update/{id}', 'update');

    // Eliminar un cliente
    Route::delete('client/delete/{id}', 'delete');
});

/**
 * Rutas relacionadas con los tipos de cliente.
 */
Route::controller(clientTipoController::class)->group(function () {
    // Obtener todos los tipos de cliente
    Route::get('client/type/all', 'read');

    // Obtener tipos específicos de cliente
    Route::get('client/type/documents', 'readDoc');
    Route::get('client/type/obligations', 'readObl');
    Route::get('client/type/people', 'readPer');
    Route::get('client/type/regimens', 'readReg');
});

/**
 * Rutas relacionadas con las habitaciones.
 */
Route::controller(RoomController::class)->group(function () {
    // Crear una nueva habitación
    Route::post('room/create', 'create');

    // Obtener información de las habitaciones
    Route::get('room/read', 'read');
    Route::get('room/read/client', 'readClient');

    // Encontrar información de una habitación por ID
    Route::get('room/find/{id}', 'find');

    // Actualizar información de una habitación
    Route::patch('room/update/{id}', 'update');

    // Actualizar imagenes de una habitación
    Route::post('room/img/{id}', 'updateImg');

    // Eliminar una habitación
    Route::delete('room/delete/{id}', 'delete');

    // Guardar precios de una habitación
    Route::post('room/precios/{id}', 'savePrecios');

    // Obtener precios de una habitación
    Route::get('room/precios/{id}', 'getPrecios');

    // Obtener jornadas
    Route::get('jornadas/read', 'getJornadas');

    // Actualizar estado de las habitaciones similares
    Route::patch('room/estados', 'updateEstado');
});

/**
 * Rutas relacionadas con los tipos de habitación.
 */
Route::controller(RoomTipoController::class)->group(function () {
    // Crear un nuevo tipo de habitación
    Route::post('room/type/create', 'create');

    // Obtener información de tipos de habitación
    Route::get('room/type', 'read');
    Route::get('room/type/{id}', 'find');

    // Actualizar información de un tipo de habitación
    Route::patch('room/type/update/{id}', 'update');

    // Eliminar un tipo de habitación
    Route::delete('room/type/delete/{id}', 'delete');
});

/**
 * Rutas relacionadas con los estados de habitación.
 */
Route::controller(RoomEstadoController::class)->group(function () {
    // Crear un nuevo estado de habitación
    Route::post('room/estado/create', 'create');

    // Obtener información de estados de habitación
    Route::get('room/estado', 'read');
    Route::get('room/estado/{id}', 'find');

    // Actualizar información de un estado de habitación
    Route::patch('room/estado/update/{id}', 'update');

    // Eliminar un estado de habitación
    Route::delete('room/estado/delete/{id}', 'delete');
});

/**
 * Rutas relacionadas con la bitácora de cambios de habitación.
 */
Route::controller(RoomBitacoraCambioController::class)->group(function () {
    // Obtener la bitácora de cambios de una habitación
    Route::get('room/bitacora/{id}', 'read');
});

Route::controller(ConfiguracionController::class)->group(function () {
    Route::post('settings/pagos', 'pagos');
    Route::post('settings/reservar', 'reservar');
    Route::get('reservar', 'getReservaConfig');
    Route::post('settings/empresa', 'empresa');
    Route::get('settings/read', 'read');
    Route::get('settings/empresa/types', 'empresaTypes');
    Route::get('pagos', 'getPagos');
    Route::post('default', 'defaultConfig');
    Route::get('default', 'getDefaultConfig');
    Route::post('metodoPago', 'metodoPago');
});

Route::controller(DesayunoController::class)->group(function () {
    Route::post('desayunos/create', 'create');
    Route::get('desayunos/read', 'read');
    Route::get('desayunos/{id}', 'find');
    Route::post('desayunos/update/{id}', 'update');
    Route::delete('desayunos/delete/{id}', 'delete');
});

Route::controller(DecoracionController::class)->group(function () {
    Route::post('decoraciones/create', 'create');
    Route::get('decoraciones/read', 'read');
    Route::get('decoraciones/{id}', 'find');
    Route::post('decoraciones/update/{id}', 'update');
    Route::delete('decoraciones/delete/{id}', 'delete');
});

Route::controller(CaracteristicasController::class)->group(function () {
    Route::post('room/caracteristicas/create', 'create');
    Route::get('room/caracteristicas/read', 'read');
    Route::get('room/caracteristicas/{id}', 'find');
    Route::patch('room/caracteristicas/update/{id}', 'update');
    Route::delete('room/caracteristicas/delete/{id}', 'delete');
});

Route::controller(CancelacionTipoController::class)->group(function () {
    Route::post('cancelar/tipo/create', 'create');
    Route::get('cancelar/tipo/read', 'read');
    Route::get('cancelar/tipo/{id}', 'find');
    Route::get('cancelacion/{id}','cancelacionByReserva');
    Route::patch('cancelar/tipo/update/{id}', 'update');
    Route::delete('cancelar/tipo/delete/{id}', 'delete');
});

Route::controller(PaisController::class)->group(function (){
    Route::post('pais', 'create');
    Route::get('paises', 'read');
});

Route::controller(DepartamentoController::class)->group(function (){
    Route::post('departamento', 'create');
    Route::get('departamentos/{id}', 'read');
});

Route::controller(MunicipioController::class)->group(function (){
    Route::post('municipio', 'create');
    Route::get('municipios/{id}', 'read');
});

Route::controller(DivisasController::class)->group(function (){
    Route::post('divisa', 'create');
    Route::get('divisas', 'read');
    Route::get('divisa/{id}', 'find');
    Route::patch('divisa/{id}', 'update');
    Route::delete('divisa/{id}', 'delete');
});

Route::controller(ImpuestoController::class)->group(function (){
    Route::post('impuesto', 'create');
    Route::get('impuestos', 'read');
    Route::get('impuesto/tipos', 'readTipos');
    Route::get('impuesto/{id}', 'find');
    Route::patch('impuesto/{id}', 'update');
    Route::delete('impuesto/{id}', 'delete');
});

Route::controller(TarifasController::class)->group(function (){
    Route::post('tarifa', 'save');
    Route::delete('tarifa', 'delete');
});