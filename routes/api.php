<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/user/logout', [AuthController::class, 'logout']);

    Route::post('/kost', [KostController::class, 'store']);
    Route::get('/kost', [KostController::class, 'index']);
    Route::patch('/kost/{id}', [KostController::class, 'update']);
    Route::delete('/kost/{id}', [KostController::class, 'delete']);
});

Route::post('/user/register', [AuthController::class, 'register']);
Route::post('/user/login', [AuthController::class, 'login']);

Route::get('/kost/search', [KostController::class, 'search']);
Route::get('/kost/{id}', [KostController::class, 'show']);
