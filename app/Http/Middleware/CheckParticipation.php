<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckParticipation
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
        $userId = Auth::id();
        $tournamentId = $request->get('tournament_id');
        $check = DB::table('tournaments_and_users')->where('user_id', $userId)->where('tournament_id', $tournamentId)->first();
        if ($check == null) return response()->json(['message' => 'Вы не участвуете в этом турнире', 'status' => 'error'], 400);
        return $next($request);
    }
}
