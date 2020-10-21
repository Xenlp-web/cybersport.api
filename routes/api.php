<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Users
Route::post('login', 'App\Http\Controllers\AuthController@login');
Route::post('register', 'App\Http\Controllers\AuthController@register');
Route::get('getUserInfo', 'App\Http\Controllers\UserController@getUserInfo');
Route::middleware('auth:api', 'admin_rights')->post('changeUserInfoByAdmin', 'App\Http\Controllers\UserController@changeUserInfo');
Route::middleware('auth:api', 'game_info_fullness')->post('joinTournament', 'App\Http\Controllers\UserController@joinTournament');
Route::middleware('auth:api', 'participation')->post('cancelTournamentParticipation', 'App\Http\Controllers\UserController@cancelTournamentParticipation');
Route::middleware('auth:api')->post('addGameInfo', 'App\Http\Controllers\UserController@addGameInfo');
Route::middleware('auth:api')->post('changeUserInfo', 'App\Http\Controllers\UserController@changeUserInfo');
Route::middleware('auth:api')->post('sendNewEmailConfirmationCode', 'App\Http\Controllers\UserController@sendNewEmailConfirmationCode');
Route::middleware('auth:api')->post('confirmEmail', 'App\Http\Controllers\UserController@confirmEmail');

// Games
Route::get('getAllGames', 'App\Http\Controllers\GamesController@getAllGames');

// Price
Route::get('getTicketPrice', 'App\Http\Controllers\TicketPriceController@getPrice');

// Chat
Route::get('getGlobalChatMessages', 'App\Http\Controllers\ChatController@getGlobalChatMessages');
Route::middleware('auth:api')->post('sendMessageToGlobalChat', 'App\Http\Controllers\ChatController@sendMessageToGlobalChat');

// Tournaments
Route::get('getTournamentsByGame', 'App\Http\Controllers\TournamentsController@getTournamentsByGame');
Route::middleware('auth:api', 'admin_rights')->post('createTournamentByAdmin', 'App\Http\Controllers\TournamentsController@createTournamentByAdmin');
Route::middleware('auth:api', 'admin_rights')->post('saveAutoTournOptions', 'App\Http\Controllers\TournamentsController@saveAutoTournOptions');
Route::middleware('auth:api', 'admin_rights')->post('editTournamentInfo', 'App\Http\Controllers\TournamentsController@editTournamentInfo');
Route::middleware('auth:api', 'participation')->post('getLobbyInfo', 'App\Http\Controllers\TournamentsController@getLobbyInfo');

// Statistic
Route::get('getStatisticForPlayers', 'App\Http\Controllers\StatisticController@getStatisticForPlayers');

//Regions
Route::get('getAllRegions', 'App\Http\Controllers\RegionsController@getAll');

// Errors
Route::get('errorUnauthorized', function() {
    return response()->json(['message' => 'Не авторизован', 'status' => 'error'], 401);
})->name('unathorized');
