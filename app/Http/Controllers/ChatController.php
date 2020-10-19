<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlobalChat;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected function failedValidation($validator) {
        throw new ValidationException($validator);
    }

    public function getGlobalChatMessages() {
        try {
            $messages = GlobalChat::orderBy('id', 'desc')->take(100)->get()->reverse();
            if (count($messages) < 1) throw new \Exception("Сообщений нет");
            return response()->json(['message' => 'Сообщения получены', 'status' => 'success', 'messages' => $messages], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function sendMessageToGlobalChat(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'message' => 'required'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        try {
            $user_name = $request->get('user_name');
            $message = $request->get('message');
            if (!GlobalChat::create(['message' => $message, 'user_name' => $user_name])) throw new \Exception("Ошибка при отправке сообщения");
            return response()->json(['message' => 'Сообщение отправлено', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }
}
