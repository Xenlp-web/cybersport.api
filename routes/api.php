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
Route::middleware('auth:api', 'admin_rights')->post('changeUserInfoByAdmin', 'App\Http\Controllers\UserController@changeUserInfo');
Route::middleware('auth:api')->post('joinTournament', 'App\Http\Controllers\UserController@joinTournament');

// Games
Route::get('getAllGames', 'App\Http\Controllers\GamesController@getAllGames');

// Price
Route::get('getTicketPrice', 'App\Http\Controllers\TicketPriceController@getPrice');

// Chat
Route::get('getGlobalChatMessages', 'App\Http\Controllers\ChatController@getGlobalChatMessages');
Route::middleware('auth:api')->post('sendMessageToGlobalChat', 'App\Http\Controllers\ChatController@sendMessageToGlobalChat');

// Tournaments
Route::get('getTournamentsByGame', 'App\Http\Controllers\TournamentsController@getTournamentsByGame');
Route::middleware('auth:api', 'admin_rights')->post('createTounamentByAdmin', 'App\Http\Controllers\TournamentsController@createTounamentByAdmin');
Route::middleware('auth:api', 'admin_rights')->post('createTounamentByAdmin', 'App\Http\Controllers\TournamentsController@saveAutoTournOptions');

// Errors
Route::get('errorUnauthorized', function() {
    return response()->json(['message' => 'Не авторизован', 'status' => 'error'], 401);
})->name('unathorized');
