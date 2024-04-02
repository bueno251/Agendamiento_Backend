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
use App\Http\Controllers\ConfigFormReservaController;
use App\Http\Controllers\CuponesController;
use App\Http\Controllers\DescuentoLargaEstadiaController;
use App\Http\Controllers\DescuentosController;
use App\Http\Controllers\DireccionesController;
use App\Http\Controllers\DivisasController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\ReservaMotivosController;
use App\Http\Controllers\TarifasController;
use App\Http\Controllers\TarifasEspecialesController;
use App\Http\Controllers\TarifasGeneralesController;
use App\Http\Controllers\TarifasOtasController;
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

    // Cancelar una reserva existente
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

/**
 * Rutas relacionadas con la configuración.
 */
Route::controller(ConfiguracionController::class)->group(function () {
    // Configuración de pagos
    Route::post('settings/pagos', 'pagos');

    // Configuración de reserva
    Route::post('settings/reservar', 'reservar');

    // Obtener configuración de reserva
    Route::get('reservar', 'getReservaConfig');

    // Configuración de la empresa
    Route::post('settings/empresa', 'empresa');

    // Obtener configuración general
    Route::get('settings', 'read');

    // Obtener tipos de empresa
    Route::get('settings/empresa/tipos', 'empresaTypes');

    // Obtener configuración de pagos
    Route::get('pagos', 'getPagos');

    // Configuración predeterminada
    Route::post('default', 'defaultConfig');

    // Obtener configuración predeterminada
    Route::get('default', 'getDefaultConfig');

    // Configurar método de pago
    Route::post('metodoPago', 'metodoPago');
});

/**
 * Rutas relacionadas con los desayunos.
 */
Route::controller(DesayunoController::class)->group(function () {
    // Crear un nuevo desayuno
    Route::post('desayuno', 'create');

    // Obtener todos los desayunos
    Route::get('desayunos', 'read');

    // Obtener un desayuno por su ID
    Route::get('desayuno/{id}', 'find');

    // Actualizar un desayuno existente
    Route::post('desayuno/{id}', 'update');

    // Eliminar un desayuno existente
    Route::delete('desayuno/{id}', 'delete');
});

/**
 * Rutas relacionadas con el control de decoraciones.
 */
Route::controller(DecoracionController::class)->group(function () {
    // Crear una nueva decoración
    Route::post('decoracion', 'create');

    // Obtener todas las decoraciones
    Route::get('decoraciones', 'read');

    // Obtener una decoración por su ID
    Route::get('decoracion/{id}', 'find');

    // Actualizar una decoración existente
    Route::post('decoracion/{id}', 'update');

    // Eliminar una decoración existente
    Route::delete('decoracion/{id}', 'delete');
});

/**
 * Rutas relacionadas con el control de características.
 */
Route::controller(CaracteristicasController::class)->group(function () {
    // Crear una nueva característica
    Route::post('caracteristica', 'create');

    // Obtener todas las características
    Route::get('caracteristicas', 'read');

    // Obtener una característica por su ID
    Route::get('caracteristica/{id}', 'find');

    // Actualizar una característica existente
    Route::patch('caracteristica/{id}', 'update');

    // Eliminar una característica existente
    Route::delete('caracteristica/{id}', 'delete');
});

/**
 * Rutas relacionadas con el control de tipos de cancelación.
 */
Route::controller(CancelacionTipoController::class)->group(function () {
    // Crear un nuevo tipo de cancelación
    Route::post('cancelar/tipo', 'create');

    // Obtener todos los tipos de cancelación
    Route::get('cancelar/tipos', 'read');

    // Obtener un tipo de cancelación por su ID
    Route::get('cancelar/tipo/{id}', 'find');

    // Obtener cancelación por ID de reserva
    Route::get('cancelacion/{id}', 'cancelacionByReserva');

    // Actualizar un tipo de cancelación existente
    Route::patch('cancelar/tipo/{id}', 'update');

    // Eliminar un tipo de cancelación existente
    Route::delete('cancelar/tipo/{id}', 'delete');
});

/**
 * Rutas relacionadas con el control de divisas.
 */
Route::controller(DivisasController::class)->group(function () {
    // Crear una nueva divisa
    Route::post('divisa', 'create');

    // Obtener todas las divisas
    Route::get('divisas', 'read');

    // Obtener una divisa por su ID
    Route::get('divisa/{id}', 'find');

    // Actualizar una divisa existente
    Route::patch('divisa/{id}', 'update');

    // Eliminar una divisa existente
    Route::delete('divisa/{id}', 'delete');
});

/**
 * Rutas relacionadas con el control de impuestos.
 */
Route::controller(ImpuestoController::class)->group(function () {
    // Crear un nuevo impuesto
    Route::post('impuesto', 'create');

    // Obtener todos los impuestos
    Route::get('impuestos', 'read');

    // Obtener todos los tipos de impuestos
    Route::get('impuesto-tipos', 'readTipos');

    // Obtener un impuesto por su ID
    Route::get('impuesto/{id}', 'find');

    // Actualizar un impuesto existente
    Route::patch('impuesto/{id}', 'update');

    // Eliminar un impuesto existente
    Route::delete('impuesto/{id}', 'delete');
});

/**
 * Rutas relacionadas con el control de tarifas.
 */
Route::controller(TarifasController::class)->group(function () {
    // Guardar una nueva tarifa
    Route::post('tarifa', 'save');

    // Guardar las tarifas para un ID específico
    Route::post('tarifas/{id}', 'saveTarifas');

    // Obtener las tarifas para un ID específico
    Route::get('tarifas/{id}', 'getTarifas');

    // Obtener las jornadas
    Route::get('jornadas', 'getJornadas');

    // Eliminar una tarifa existente
    Route::delete('tarifa/{id}', 'delete');
});

/**
 * Rutas relacionadas con el control de tarifas generales.
 */
Route::controller(TarifasGeneralesController::class)->group(function () {
    // Guardar una nueva tarifa general
    Route::post('tarifas-generales', 'save');

    // Obtener todas las tarifas generales
    Route::get('tarifas-generales', 'read');

    // Eliminar una tarifa general por su ID
    Route::delete('tarifa-general/{id}', 'delete');
});

/**
 * Rutas relacionadas con el control de tarifas especiales.
 */
Route::controller(TarifasEspecialesController::class)->group(function () {
    // Crear una nueva tarifa especial
    Route::post('tarifa-especial', 'create');

    // Obtener todas las tarifas especiales para un ID específico
    Route::get('tarifas-especiales/{id}', 'read');

    // Obtener una tarifa especial por su ID
    Route::get('tarifa-especial/{id}', 'find');

    // Actualizar una tarifa especial existente
    Route::patch('tarifa-especial/{id}', 'update');

    // Eliminar una tarifa especial por su ID
    Route::delete('tarifa-especial/{id}', 'delete');
});

/**
 * Rutas relacionadas con el control de direcciones.
 */
Route::controller(DireccionesController::class)->group(function () {
    // Obtener todos los países
    Route::get('paises', 'getPaises');

    // Obtener departamentos por ID de país
    Route::get('departamentos-{id}', 'getDepartamentos');

    // Obtener ciudades por ID de departamento
    Route::get('ciudades-{id}', 'getCiudades');
});

/**
 * Rutas relacionadas con el control de motivos de reserva.
 */
Route::controller(ReservaMotivosController::class)->group(function () {
    // Obtener todos los motivos de reserva
    Route::get('reserva-motivos', 'read');
});

/**
 * Rutas relacionadas con el control de descuentos.
 */
Route::controller(DescuentosController::class)->group(function () {
    // Crear un nuevo descuento
    Route::post('descuento', 'create');

    // Obtener todos los descuentos
    Route::get('descuentos', 'read');

    // Obtener todos los tipos de descuentos
    Route::get('descuento-tipos', 'readTipos');

    // Obtener los datos importantes de las habitaciones
    Route::get('descuento-rooms', 'readRooms');

    // Obtener descuentos por ID de habitación
    Route::get('descuentos/{id}', 'readByRoom');

    // Actualizar un descuento existente
    Route::patch('descuento/{id}', 'update');

    // Eliminar un descuento existente
    Route::delete('descuento/{id}', 'delete');
});

/**
 * Rutas relacionadas con el control de cupones.
 */
Route::controller(CuponesController::class)->group(function () {
    // Crear un nuevo cupón
    Route::post('cupon', 'create');

    // Obtener todos los cupóns
    Route::get('cupones', 'read');

    Route::get('precios', 'getPrecios');

    // Obtener cupóns por ID de habitación
    Route::get('cupones/{id}', 'readByRoom');

    Route::get('cupones-{codigo}-{id}', 'chekCuponCode');

    // Actualizar un cupón existente
    Route::patch('cupon/{id}', 'update');

    Route::patch('cupones', 'updateCodes');

    // Eliminar un cupón existente
    Route::delete('cupon/{id}', 'delete');
});

Route::controller(ConfigFormReservaController::class)->group(function () {
    Route::post('formReserva', 'saveConfig');
    Route::get('formReserva', 'getConfig');
});

Route::controller(DescuentoLargaEstadiaController::class)->group(function () {
    Route::post('descuento-estadia', 'create');
    Route::get('descuentos-estadia', 'read');
    Route::get('descuentos-estadia/{id}', 'readByRoom');
    Route::patch('descuento-estadia/{id}', 'update');
    Route::delete('descuento-estadia/{id}', 'delete');
});

Route::controller(TarifasOtasController::class)->group(function(){
    Route::post('tarifas-otas', 'save');
    // Route::get('tarifas-otas', 'save');
});
