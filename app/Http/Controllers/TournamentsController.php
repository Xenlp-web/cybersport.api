<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournaments;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GamesController;

class TournamentsController extends Controller
{
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

            $newTournament = [
                'title' => $title,
                'game_id' => $game_id,
                'tickets' => $tickets,
                'img' => $img,
                'start_time' => $start_time
            ];

            $options['lobby_pass'] = substr(str_shuffle('123456789abcdefghijklmnpqrstuvwxyz'), 0, 8);
            $newTournamentOptions = $options;

            if (!self::createNewTournament($newTournament, $options ,$gameSlug)) {
                return false;
            };
        }
        return true;
    }

    private static function createNewTournament(Array $newTournament, Array $newTournamentOptions, String $gameSlug) {
        $tournamentsInfoTable = $gameSlug.'_tournaments_info';

        //TODO: Разобраться с пустыми переменными
        $title;
        $img;
        $tournament_id = DB::table('tournaments')->insertGetId($newTournament);
        $map;
        $pov;
        $newTournamentOptions['tournament_id'] = $tournament_id;
        if (DB::table($tournamentsInfoTable)->insert($newTournamentOptions)) {
            return true;
        }
        return false;
    }
}
