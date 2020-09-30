<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user();
            $token = $user->createToken('access_token')->accessToken;
            return response()->json(['message' => 'Вход выполнен успешно', 'status' => 'success', 'token' => $token, 'user_data' => $user], 200);
        }
        return response()->json(['message' => 'Неверный логин или пароль', 'status' => 'error'], 401);
    }


    public function register(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
            'password_confirm' => 'required|same:password'
        ]);

        if ($request->input('password') != $request->input('password_confirm'))
            return response()->json(['message' => 'Пароли не совпадают', 'status' => 'error'], 401);

        $newUserArr = [
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password'))
        ];

        try {
            $user = User::create($newUserArr);
            $token = $user->createToken('access_token')->accessToken;
            return response()->json(['message' => 'Аккаунт успешно зарегистрирован', 'status' => 'success', 'token' => $token, 'user_data' => $user], 200);
        } catch (\Exception $e) {
            if ($e->getCode() == 23000) {
                return response()->json(['message' => 'Ошибка: такой Email уже существует', 'status' => 'error'], 401);
            }
        }
    }
}
