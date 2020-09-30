<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Users
Route::post('login', 'App\Http\Controllers\AuthController@login');
Route::post('register', 'App\Http\Controllers\AuthController@register');
Route::get('getUserInfo', 'App\Http\Controllers\UserController@getUserInfo');

// Games
Route::get('getAllGames', 'App\Http\Controllers\GamesController@getAllGames');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
