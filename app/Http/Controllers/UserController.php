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

    public function changeUserInfoByAdmin(Request $request) {
        $userInfo = $request->get('user_info');
        try {
            if (!is_array($userInfo)) throw new \Exception('user_info не массив');
            User::where('id', $userInfo['id'])->update($userInfo);
            return response()->json(['message' => 'Информация пользователя успешно изменена', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }
}
