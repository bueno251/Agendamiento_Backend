<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\clientTipoController;
use App\Http\Controllers\ConfiguracionController;
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

// Route::controller(ReservasController::class)->group(function () {
//     Route::post('reserva/create', 'create');
//     Route::get('reserva/read', 'read');
//     Route::get('reserva/find/{id}', 'find');
//     Route::delete('reserva/delete/{dia}', 'delete');
// });

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
    Route::get('room/find/{id}', 'find');
    Route::patch('room/update/{id}', 'update');
    Route::delete('room/delete/{id}', 'delete');
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

Route::controller(ConfiguracionController::class)->group(function () {
    Route::post('settings/pagos', 'pagos');
    Route::post('settings/reservar', 'reservar');
    Route::post('settings/empresa', 'empresa');
    Route::get('settings/read', 'read');
    Route::get('settings/empresa/types', 'empresaTypes');
});
