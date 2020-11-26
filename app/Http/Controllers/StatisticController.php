<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GamesController;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class StatisticController extends Controller
{

    protected function failedValidation($validator) {
        throw new ValidationException($validator);
    }

    public function getStatisticForPlayers(Request $request) {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|integer',
            'stat_item' => 'required|string',
            'period' => 'string'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $gameId = $request->get('game_id');
        $statItem = $request->get('stat_item');
        $period = 'all';

        if ($request->has('period')) $period = $request->get('period');

        try {
            (string) $statTable = $this->getStatTable($gameId);
            if (!$statTable) throw new \Exception("Не найдена таблица");

            switch ($period) {
                case 'day':
                    $statistic = DB::table($statTable)->join('users', 'users.id', '=', $statTable.'.user_id')->select('users.id as user_id', 'users.nickname', DB::raw("SUM($statItem) as $statItem"))->where(DB::raw('DAY('.$statTable.'.created_at)'), date('j', time()))->where(DB::raw('MONTH('.$statTable.'.created_at)'), date('n', time()))->where(DB::raw('YEAR('.$statTable.'.created_at)'), date('Y', time()))->get();
                    break;

                case 'week':
                    $statistic = DB::table($statTable)->join('users', 'users.id', '=', $statTable.'.user_id')->select('users.id as user_id', 'users.nickname', DB::raw("SUM($statItem) as $statItem"))->where(DB::raw('WEEK('.$statTable.'.created_at)'), date('W', time()))->where(DB::raw('YEAR('.$statTable.'.created_at)'), date('Y', time()))->get();
                    break;

                case 'month':
                    $statistic = DB::table($statTable)->join('users', 'users.id', '=', $statTable.'.user_id')->select('users.id as user_id', 'users.nickname', DB::raw("SUM($statItem) as $statItem"))->where(DB::raw('MONTH('.$statTable.'.created_at)'), date('n', time()))->where(DB::raw('YEAR('.$statTable.'.created_at)'), date('Y', time()))->get();
                    break;

                default:
                    $statistic = DB::table($statTable)->join('users', 'users.id', '=', $statTable.'.user_id')->select('users.id as user_id', 'users.nickname', DB::raw("SUM($statItem) as $statItem"))->get();
                    break;
            }

            if (!isset($statistic)) throw new \Exception("Ошибка получения статистики");

            if ($statistic[0]->user_id == null) throw new \Exception("Нет данных для получения статистики");

            return response()->json(['message' => 'Статистика получена', 'statistic' => $statistic, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public static function saveStatistic(array $statistic, int $gameId) {
        (string) $statTable = self::getStatTable($gameId);
        DB::table($statTable)->insert($statistic);
    }

    protected static function getStatTable(int $gameId) {
        $game = GamesController::getGameById($gameId);
        if (!$game) return false;
        $gameSlug = $game->slug;
        $statTable = $gameSlug . '_statistic';
        return $statTable;
    }
}
