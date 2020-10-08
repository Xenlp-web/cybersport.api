<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournaments;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GamesController;

class TournamentsController extends Controller
{
    public function createNewTournamentsBySchedule() {
        $schedule = DB::table('auto_options_schedule')->select('game_id', 'option_id', 'day_of_week', 'time')->distinct()->get();
        if (empty($schedule)) return false;

        foreach($schedule as $record) {
            $currentDayOfWeek = date('w', time());
            $currentTime = date('H:i:s', time("+1 hour"));
            if ($currentDayOfWeek != $record->day_of_week || $currentTime != $record->time) return false;

            $game = GamesController::getGameById($record->game_id);
            if (!$game || $game == null) return false;
            $gameSlug = $game->slug;

            $optionsTable = $gameSlug.'_auto_tourn_options';
            $tournamentsInfoTable = $gameSlug.'_tournaments_info';

            $options = DB::table($optionsTable)->where('id', $record->option_id)->distinct()->first();
            if ($options == null) return false;

            //TODO: заполнить недостающие поля, в таблице tournaments добавить поле region (ru, eu, us), валюта
            //tournaments
            $title;
            $game_id = $record->game_id;
            $tickets = $options->tickets;
            $img;
            $start_time;

            $tournament_id = DB::table('tournaments')->insertGetId(
                [
                    'title' => $title,
                    'game_id' => $game_id,
                    'tickets' => $tickets,
                    'img' => $img,
                    'start_time' => $start_time
                ]
            );


            //tournaments_info
            $map;
            $pov;
            $lobby_pass;
            $mode = $options->mode;
            $kill_award = $options->kill_award;
            $mvp_award = $options->mvp_award;
            $max_players = $options->max_players;
            $placement_award = $options->placement_award;
            $winnners = $options->winners;

            //TODO: написать запрос в БД
        }
    }
}
