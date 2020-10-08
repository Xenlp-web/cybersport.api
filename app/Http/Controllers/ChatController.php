<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlobalChat;

class ChatController extends Controller
{
    public function getGlobalChatMessages() {
        try {
            $messages = GlobalChat::take(100)->get();
            if (count($messages) < 1) throw new \Exception("Сообщений нет");
            return response()->json(['message' => 'Сообщения получены', 'status' => 'success', 'messages' => $messages], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function sendMessageToGlobalChat(Request $request) {
        try {
            if (!$request->has('user_id')) throw new \Exception("Нет ID пользователя");
            $user_id = $request->get('user_id');
            $message = $request->get('message');
            if (!GlobalChat::create(['message' => $message, 'user_id' => $user_id])) throw new \Exception("Ошибка при отправке сообщения");
            return response()->json(['message' => 'Сообщение отправлено', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }
}
