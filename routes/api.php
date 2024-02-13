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

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
});

Route::controller(ReservasController::class)->group(function () {
    Route::post('reserva/create', 'create');
    Route::post('reserva/pagar', 'pagar');
    Route::get('reserva/read', 'read');
    Route::get('reserva/room/{id}', 'getDates');
    Route::get('reserva/room/{id}/{user}', 'getReservaTemporal');
    Route::get('reserva/{id}', 'reservaUser');
    Route::patch('reserva/approve/{id}', 'approve');
    Route::patch('reserva/reject/{id}', 'reject');
});

Route::controller(ClientController::class)->group(function () {
    Route::post('client/create', 'create');
    Route::get('client/read', 'read');
    Route::get('client/find/{id}', 'find');
    Route::get('client/find/document/{doc}', 'findDoc');
    Route::patch('client/update/{id}', 'update');
    Route::delete('client/delete/{id}', 'delete');
});

Route::controller(clientTipoController::class)->group(function () {
    Route::get('client/type/all', 'read');
    Route::get('client/type/documents', 'readDoc');
    Route::get('client/type/obligations', 'readObl');
    Route::get('client/type/people', 'readPer');
    Route::get('client/type/regimens', 'readReg');
});

Route::controller(RoomController::class)->group(function () {
    Route::post('room/create', 'create');
    Route::get('room/read', 'read');
    Route::get('room/read/client', 'readClient');
    Route::get('room/find/{id}', 'find');
    Route::patch('room/update/{id}', 'update');
    Route::post('room/img/{id}', 'updateImg');
    Route::delete('room/delete/{id}', 'delete');
    Route::post('room/precios/{id}', 'savePrecios');
    Route::get('room/precios/{id}', 'getPrecios');
    Route::get('jornadas/read', 'getJornadas');
    Route::patch('room/estados', 'updateEstado');
});

Route::controller(RoomTipoController::class)->group(function () {
    Route::post('room/type/create', 'create');
    Route::get('room/type', 'read');
    Route::get('room/type/{id}', 'find');
    Route::patch('room/type/update/{id}', 'update');
    Route::delete('room/type/delete/{id}', 'delete');
});

Route::controller(RoomEstadoController::class)->group(function () {
    Route::post('room/estado/create', 'create');
    Route::get('room/estado', 'read');
    Route::get('room/estado/{id}', 'find');
    Route::patch('room/estado/update/{id}', 'update');
    Route::delete('room/estado/delete/{id}', 'delete');
});

Route::controller(RoomBitacoraCambioController::class)->group(function () {
    Route::get('room/bitacora/{id}', 'read');
});

Route::controller(ConfiguracionController::class)->group(function () {
    Route::post('settings/pagos', 'pagos');
    Route::post('settings/reservar', 'reservar');
    Route::post('settings/empresa', 'empresa');
    Route::get('settings/read', 'read');
    Route::get('settings/empresa/types', 'empresaTypes');
    Route::get('pagos', 'getPagos');
    Route::post('default', 'defaultConfig');
    Route::get('default', 'getDefaultConfig');
});

Route::controller(DesayunoController::class)->group(function () {
    Route::post('desayunos/create', 'create');
    Route::get('desayunos/read', 'read');
    Route::get('desayunos/{id}', 'find');
    Route::patch('desayunos/update/{id}', 'update');
    Route::delete('desayunos/delete/{id}', 'delete');
});

Route::controller(DecoracionController::class)->group(function () {
    Route::post('decoraciones/create', 'create');
    Route::get('decoraciones/read', 'read');
    Route::get('decoraciones/{id}', 'find');
    Route::patch('decoraciones/update/{id}', 'update');
    Route::delete('decoraciones/delete/{id}', 'delete');
});

Route::controller(CaracteristicasController::class)->group(function () {
    Route::post('room/caracteristicas/create', 'create');
    Route::get('room/caracteristicas/read', 'read');
    Route::get('room/caracteristicas/{id}', 'find');
    Route::patch('room/caracteristicas/update/{id}', 'update');
    Route::delete('room/caracteristicas/delete/{id}', 'delete');
});
