<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Games;

class GamesController extends Controller
{
    public function getAllGames() {
        try {
            $games = Games::all();
            if ($games === null) throw new \Exception('Не найдены записи в БД');
            return response()->json(['message' => 'Список игр получен', 'games' => $games, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }
}
