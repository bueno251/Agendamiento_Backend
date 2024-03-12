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
use App\Http\Controllers\DireccionesController;
use App\Http\Controllers\DivisasController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\ReservaMotivosController;
use App\Http\Controllers\TarifasController;
use App\Http\Controllers\TarifasEspecialesController;
use App\Http\Controllers\TarifasGeneralesController;
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
    Route::post('reserva', 'create');

    // Pagar una reserva temporal existente
    Route::post('reserva/pagar', 'pagar');

    // Obtener información de las reservas
    Route::get('reservas/{estado?}', 'read');

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
    Route::post('cliente', 'create');

    // Obtener información de los clientes
    Route::get('clientes', 'read');

    // Encontrar información de un cliente por ID
    Route::get('cliente/{id}', 'find');

    // Encontrar información de un cliente por número de documento
    Route::get('cliente/documento/{doc}', 'findDoc');

    // Actualizar información de un cliente
    Route::patch('cliente/{id}', 'update');

    // Eliminar un cliente
    Route::delete('cliente/{id}', 'delete');
});

/**
 * Rutas relacionadas con los tipos de cliente.
 */
Route::controller(clientTipoController::class)->group(function () {
    // Obtener todos los tipos de cliente
    Route::get('cliente-tipos', 'read');
    Route::get('cliente-tipo-documentos', 'documento');
});

/**
 * Rutas relacionadas con las habitaciones.
 */
Route::controller(RoomController::class)->group(function () {
    // Crear una nueva habitación
    Route::post('room', 'create');

    // Obtener información de las habitaciones
    Route::get('rooms/read', 'read');
    Route::get('rooms', 'readClient');

    // Encontrar información de una habitación por ID
    Route::get('room/{id}', 'find');

    // Actualizar información de una habitación
    Route::patch('room/{id}', 'update');

    // Actualizar imagenes de una habitación
    Route::post('room/img/{id}', 'updateImg');

    // Eliminar una habitación
    Route::delete('room/{id}', 'delete');
    Route::delete('room-hija/{id}', 'deleteHija');

    // Actualizar estado de las habitaciones similares
    Route::patch('rooms', 'updateRooms');
});

/**
 * Rutas relacionadas con los tipos de habitación.
 */
Route::controller(RoomTipoController::class)->group(function () {
    // Crear un nuevo tipo de habitación
    Route::post('tipo-room', 'create');

    // Obtener información de tipos de habitación
    Route::get('tipos-room', 'read');
    Route::get('tipo-room/{id}', 'find');

    // Actualizar información de un tipo de habitación
    Route::patch('tipo-room/{id}', 'update');

    // Eliminar un tipo de habitación
    Route::delete('tipo-room/{id}', 'delete');
});

/**
 * Rutas relacionadas con los estados de habitación.
 */
Route::controller(RoomEstadoController::class)->group(function () {
    // Crear un nuevo estado de habitación
    Route::post('estado-room', 'create');

    // Obtener información de estados de habitación
    Route::get('estados-room', 'read');
    Route::get('estado-room/{id}', 'find');

    // Actualizar información de un estado de habitación
    Route::patch('estado-room/{id}', 'update');

    // Eliminar un estado de habitación
    Route::delete('estado-room/{id}', 'delete');
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
    Route::get('settings', 'read');
    Route::get('settings/empresa/tipos', 'empresaTypes');
    Route::get('pagos', 'getPagos');
    Route::post('default', 'defaultConfig');
    Route::get('default', 'getDefaultConfig');
    Route::post('metodoPago', 'metodoPago');
});

Route::controller(DesayunoController::class)->group(function () {
    Route::post('desayuno', 'create');
    Route::get('desayunos', 'read');
    Route::get('desayuno{id}', 'find');
    Route::post('desayuno/{id}', 'update');
    Route::delete('desayuno/{id}', 'delete');
});

Route::controller(DecoracionController::class)->group(function () {
    Route::post('decoracion', 'create');
    Route::get('decoraciones', 'read');
    Route::get('decoracion{id}', 'find');
    Route::post('decoracion/{id}', 'update');
    Route::delete('decoracion/{id}', 'delete');
});

Route::controller(CaracteristicasController::class)->group(function () {
    Route::post('caracteristica', 'create');
    Route::get('caracteristicas', 'read');
    Route::get('caracteristica/{id}', 'find');
    Route::patch('caracteristica/{id}', 'update');
    Route::delete('caracteristica/{id}', 'delete');
});

Route::controller(CancelacionTipoController::class)->group(function () {
    Route::post('cancelar/tipo', 'create');
    Route::get('cancelar/tipos', 'read');
    Route::get('cancelar/tipo/{id}', 'find');
    Route::get('cancelacion/{id}','cancelacionByReserva');
    Route::patch('cancelar/tipo{id}', 'update');
    Route::delete('cancelar/tipo{id}', 'delete');
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
    Route::post('tarifas/{id}', 'saveTarifas');
    Route::get('tarifas/{id}', 'getTarifas');
    Route::get('jornadas', 'getJornadas');
    Route::delete('tarifa/{id}', 'delete');
});

Route::controller(TarifasGeneralesController::class)->group(function (){
    Route::post('tarifas-generales', 'save');
    Route::get('tarifas-generales', 'read');
    Route::delete('tarifa-general/{id}', 'delete');
});

Route::controller(TarifasEspecialesController::class)->group(function () {
    Route::post('tarifa-especial', 'create');
    Route::get('tarifas-especiales/{id}', 'read');
    Route::get('tarifa-especial/{id}', 'find');
    Route::patch('tarifa-especial/{id}', 'update');
    Route::delete('tarifa-especial/{id}', 'delete');
});

Route::controller(DireccionesController::class)->group(function ( ) {
    Route::get('paises', 'getPaises');
    Route::get('departamentos-{id}', 'getDepartamentos');
    Route::get('ciudades-{id}', 'getCiudades');
});

Route::controller(ReservaMotivosController::class)->group(function () {
    Route::get('reserva-motivos', 'read');
});