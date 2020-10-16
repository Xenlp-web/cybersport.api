<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GamesController;

class StatisticController extends Controller
{
    public function getStatisticForPlayers(Request $request) {
        $this->validate($request, [
            'game_id' => 'required|integer',
            'stat_item' => 'required|string',
            'period' => 'string'
        ]);

        (int) $gameId = $request->get('game_id');
        (string) $statItem = $request->get('stat_item');
        (string) $period = 'all';

        if ($request->has('period')) $period = $request->get('period');

        try {
            (string) $statTable = $this->getStatTable($gameId);
            if (!$statTable) throw new \Exception("Не найдена таблица");

            switch ($period) {
                case 'day':
                    $statistic = DB::table($statTable)->select('user_id', $statItem)->where(DB::raw('DAY(created_at)'), date('j', time()))->where(DB::raw('MONTH(created_at)'), date('n', time()))->where(DB::raw('YEAR(created_at)'), date('Y', time()))->get();
                    break;

                case 'week':
                    $statistic = DB::table($statTable)->select('user_id', $statItem)->where(DB::raw('WEEK(created_at)'), date('W', time()))->where(DB::raw('YEAR(created_at)'), date('Y', time()))->get();
                    break;

                case 'month':
                    $statistic = DB::table($statTable)->select('user_id', $statItem)->where(DB::raw('MONTH(created_at)'), date('n', time()))->where(DB::raw('YEAR(created_at)'), date('Y', time()))->get();
                    break;

                default:
                    $statistic = DB::table($statTable)->select('user_id', $statItem)->get();
                    break;
            }

            if (!isset($statistic)) throw new \Exception("Ошибка получения статистики");

            return response()->json(['message' => 'Статистика получена', 'statistic' => $statistic, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    protected function getStatTable(int $gameId) {
        $game = GamesController::getGameById($gameId);
        if (!$game) return false;
        $gameSlug = $game->slug;
        $statTable = $gameslug . '_statistic';
        return $statTable;
    }
}
