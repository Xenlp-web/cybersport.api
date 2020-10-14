<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GamesController;

class GameInfoFullness
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->get('user_id');
        $gameId = $request->get('game_id');
        $game = GamesController::getGameById($gameId);
        $gameSlug = $game->slug;
        $tableName = $gameSlug.'_info';
        $check = DB::table($tableName)->where('user_id', $userId)->first();
        if ($check == null) return response()->json(['message' => 'Не заполнены данные для игры', 'status' => 'error'], 400);
        return $next($request);
    }
}
