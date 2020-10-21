<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmationCode;


class AuthController extends Controller
{
    protected function failedValidation($validator) {
        throw new ValidationException($validator);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'password_confirm' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        if ($request->input('password') != $request->input('password_confirm'))
            return response()->json(['message' => 'Пароли не совпадают', 'status' => 'error'], 401);

        $newUserArr = [
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password'))
        ];

        try {
            $user = User::create($newUserArr);
            $token = $user->createToken('access_token')->accessToken;

            $user->referal_code = self::genRefCode();
            $user->email_confirmation_code = self::genMailConfirmationCode();
            $user->save();
            Mail::to($user)->send(new ConfirmationCode($user->email_confirmation_code));
            return response()->json(['message' => 'Аккаунт успешно зарегистрирован', 'status' => 'success', 'token' => $token, 'user_data' => $user], 200);
        } catch (\Exception $e) {
            if ($e->getCode() == 23000) {
                return response()->json(['message' => 'такой Email уже существует', 'status' => 'error'], 401);
            }
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 401);
        }
    }

    private function genRefCode() {
        $exit = false;
        while ($exit === false) {
            $refCode = substr(str_shuffle('123456789abcdefghijklmnpqrstuvwxyz'), 0, 6);
            if (User::where('referal_code', $refCode)->first() === null) {
                $exit = true;
            }
        }
        return $refCode;
    }

    public function genMailConfirmationCode() {
        $code = substr(str_shuffle('123456789abcdefghijklmnpqrstuvwxyz'), 0, 6);
        return $code;
    }
}
