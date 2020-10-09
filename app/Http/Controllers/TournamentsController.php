<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournaments;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GamesController;

class TournamentsController extends Controller
{
    public function getTournamentsByGame(Request $request) {
        try {
            if (!$request->has('game_id')) throw new \Exception("Нет id игры");
            $game_id = $request->get('game_id');
            $tournaments = Tournaments::where('game_id', $game_id)->get();
            if (count($tournaments) < 1) throw new \Exception("Нет турниров");
            return response()->json(['message' => 'Турниры найдены', 'tournaments' => $tournaments, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function createTounamentByAdmin(Request $request) {
        $game_id = $request->get('game_id');
        $title = $request->get('title');
        $tickets = $request->get('tickets');
        $img = $request->get('img');
        $start_time = $request->get('start_time');
        $region = $request->get('region');
        $options = $request->get('options');

        $newTournament = [
            'title' => $title,
            'game_id' => $game_id,
            'tickets' => $tickets,
            'img' => $img,
            'start_time' => $start_time,
            'region' => $region
        ];

        try {
            $game = GamesController::getGameById($game_id);
            $gameSlug = $game->slug;
            if (!is_array($options)) throw new \Exception('Не указаны опции для турнира');
            if (!self::createNewTournament($newTournament, $options, $gameSlug)) throw new \Exception("Ошибка при создании турнира");

            return response()->json(['message' => 'Турнир создан', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function createAutoTournamentsBySchedule() {
        $schedule = DB::table('auto_options_schedule')->select('game_id', 'option_id', 'day_of_week', 'time')->distinct()->get();
        if (empty($schedule)) return false;

        foreach($schedule as $record) {
            $currentDayOfWeek = date('w', time());
            $currentTime = date('H:i:s', time("+3 hour"));
            if ($currentDayOfWeek != $record->day_of_week || $currentTime != $record->time) return false;

            $game = GamesController::getGameById($record->game_id);
            if (!$game || $game == null) return false;
            $gameSlug = $game->slug;

            $optionsTable = $gameSlug.'_auto_tourn_options';
            $tournamentsInfoTable = $gameSlug.'_tournaments_info';

            $options = DB::table($optionsTable)->where('id', $record->option_id)->distinct()->first()->toArray();
            if ($options == null) return false;

            $game_id = $record->game_id;
            $tickets = $options['tickets'];
            $start_time = $record->time;
            $region = $record->region;
            $title;
            $img;

            $newTournament = [
                'title' => $title,
                'game_id' => $game_id,
                'tickets' => $tickets,
                'img' => $img,
                'start_time' => $start_time,
                'region' => $region
            ];

            $map;
            $pov;

            $options['lobby_pass'] = substr(str_shuffle('123456789abcdefghijklmnpqrstuvwxyz'), 0, 8);

            if (!self::createNewTournament($newTournament, $options, $gameSlug)) {
                return false;
            };
        }
        return true;
    }

    private static function createNewTournament(Array $newTournament, Array $newTournamentOptions, String $gameSlug) {
        $tournamentsInfoTable = $gameSlug.'_tournaments_info';

        $tournament_id = DB::table('tournaments')->insertGetId($newTournament);
        $newTournamentOptions['tournament_id'] = $tournament_id;
        if (DB::table($tournamentsInfoTable)->insert($newTournamentOptions)) {
            return true;
        }
        return false;
    }
}
