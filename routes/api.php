<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Users
Route::post('login', 'App\Http\Controllers\AuthController@login');
Route::post('register', 'App\Http\Controllers\AuthController@register');
Route::get('get-user-info', 'App\Http\Controllers\UserController@getUserInfo');
Route::middleware('auth:api')->get('get-current-user-info', 'App\Http\Controllers\UserController@getCurrentUserInfo');
Route::middleware('auth:api', 'admin_rights')->post('change-user-info-by-admin', 'App\Http\Controllers\UserController@changeUserInfo');
Route::middleware('auth:api', 'game_info_fullness')->post('join-tournament', 'App\Http\Controllers\UserController@joinTournament');
Route::middleware('auth:api', 'participation')->post('cancel-tournament-participation', 'App\Http\Controllers\UserController@cancelTournamentParticipation');
Route::middleware('auth:api')->post('add-game-info', 'App\Http\Controllers\UserController@addGameInfo');
Route::middleware('auth:api')->post('change-user-info', 'App\Http\Controllers\UserController@changeUserInfo');
Route::middleware('auth:api')->post('send-new-email-confirmation-code', 'App\Http\Controllers\UserController@sendNewEmailConfirmationCode');
Route::middleware('auth:api')->post('confirm-email', 'App\Http\Controllers\UserController@confirmEmail');
Route::middleware('auth:api')->post('upload-avatar', 'App\Http\Controllers\UserController@uploadAvatar');

// Games
Route::get('get-all-games', 'App\Http\Controllers\GamesController@getAllGames');

// Price
Route::get('get-ticket-price', 'App\Http\Controllers\TicketPriceController@getPrice');

// Chat
Route::get('get-global-chat-messages', 'App\Http\Controllers\ChatController@getGlobalChatMessages');
Route::middleware('auth:api')->post('send-message-to-global-chat', 'App\Http\Controllers\ChatController@sendMessageToGlobalChat');

// Tournaments
Route::get('get-tournaments-by-game', 'App\Http\Controllers\TournamentsController@getTournamentsByGame');
Route::middleware('auth:api', 'admin_rights')->post('create-tournament-by-admin', 'App\Http\Controllers\TournamentsController@createTournamentByAdmin');
Route::middleware('auth:api', 'admin_rights')->post('save-auto-tourn-options', 'App\Http\Controllers\TournamentsController@saveAutoTournOptions');
Route::middleware('auth:api', 'admin_rights')->post('edit-tournament-info', 'App\Http\Controllers\TournamentsController@editTournamentInfo');
Route::middleware('auth:api', 'participation')->post('get-lobby-info', 'App\Http\Controllers\TournamentsController@getLobbyInfo');
Route::middleware('auth:api', 'admin_rights')->post('save-result', 'App\Http\Controllers\TournamentsController@saveResult');
Route::get('get-all-streams', 'App\Http\Controllers\TournamentsController@getAllStreams');
Route::middleware('auth:api', 'admin_rights')->get('get-tournaments-for-admin', 'App\Http\Controllers\TournamentsController@getAllTournamentsForAdmin');
Route::middleware('auth:api', 'admin_rights')->get('get-tournaments-option-for-admin', 'App\Http\Controllers\TournamentsController@getTournamentsOptionForAdmin');
Route::get('get-participants', 'App\Http\Controllers\TournamentsController@getParticipants');
Route::middleware('auth:api', 'admin_rights')->delete('remove-tournament', 'App\Http\Controllers\TournamentsController@removeTournament');
Route::get('get-tournament-info', 'App\Http\Controllers\TournamentsController@getTournamentInfo');

// Statistic
Route::get('get-statistic-for-players', 'App\Http\Controllers\StatisticController@getStatisticForPlayers');

//Regions
Route::get('get-all-regions', 'App\Http\Controllers\RegionsController@getAll');

// Errors
Route::get('errorUnauthorized', function() {
    return response()->json(['message' => 'Не авторизован', 'status' => 'error'], 401);
})->name('unathorized');
