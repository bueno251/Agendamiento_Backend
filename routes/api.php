<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\clientTipoController;
use App\Http\Controllers\DayController;
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

Route::controller(DayController::class)->group(function () {
    Route::post('days/create', 'create');
    Route::get('days/read', 'read');
    Route::get('days/find/{id}', 'find');
    Route::delete('days/delete/{dia}', 'delete');
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
