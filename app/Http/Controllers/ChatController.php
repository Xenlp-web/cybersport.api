<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlobalChat;

class ChatController extends Controller
{
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
        $this->validate($request, [
            'user_name' => 'required',
            'message' => 'required'
        ]);

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
