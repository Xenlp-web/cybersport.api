<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function getUserInfo(Request $request) {
        $email = $request->get('email');
        try {
            $user = User::where('email', $email)->first();
            if ($user === null) throw new \Exception('Пользователь не найден');
            return response()->json(['message' => 'Пользователь найден', 'user' => $user, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }
}
