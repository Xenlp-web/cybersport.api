<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class CheckAdminRights
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
        $isAdmin = User::select('is_admin')->where('id', $userId)->firstOrFail();
        if ($isAdmin->is_admin != 1) return response()->json(['message' => 'У вас нет прав администратора', 'status' => 'error'], 400);
        return $next($request);
    }
}
