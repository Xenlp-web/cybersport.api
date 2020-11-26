<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournaments;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GamesController;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TournamentsController extends Controller
{
    protected function failedValidation($validator) {
        throw new ValidationException($validator);
    }

    public function getTournamentsByGame(Request $request) {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        try {
            $game_id = $request->get('game_id');
            $game = GamesController::getGameById($game_id);
            $gameSlug = $game->slug;
            $tableForGame = $gameSlug.'_tournaments_info';
            $tournamentsToday = DB::table('tournaments')
                ->join($tableForGame, $tableForGame.'.tournament_id', '=', 'tournaments.id')
                ->leftJoin('tournaments_and_users', function ($join) {
                    $userId = auth('api')->user()->id;
                    $join->on('tournaments_and_users.tournament_id', '=', 'tournaments.id')
                    ->where('tournaments_and_users.user_id', $userId);
                })
                ->select('tournaments.*', $tableForGame.'.*', 'tournaments_and_users.user_id as participation')
                ->where('game_id', $game_id)
                ->whereDate('start_time', Carbon::today())
                ->get();
            $tournamentsTomorrow = DB::table('tournaments')
                ->join($tableForGame, $tableForGame.'.tournament_id', '=', 'tournaments.id')
                ->leftJoin('tournaments_and_users', function ($join) {
                    $userId = auth('api')->user()->id;
                    $join->on('tournaments_and_users.tournament_id', '=', 'tournaments.id')
                    ->where('tournaments_and_users.user_id', $userId);
                })
                ->select('tournaments.*', $tableForGame.'.*', 'tournaments_and_users.user_id as participation')
                ->where('game_id', $game_id)
                ->whereDate('start_time', Carbon::tomorrow())
                ->get();
            $tournamentsEnded = DB::table('tournaments')
                ->join($tableForGame, $tableForGame.'.tournament_id', '=', 'tournaments.id')
                ->where('game_id', $game_id)->where('ended', 1)
                ->get();
            $tournaments = [
                'tournamentsToday' => $tournamentsToday,
                'tournamentsTomorrow' => $tournamentsTomorrow,
                'tournamentsEnded' => $tournamentsEnded
            ];
            if (count($tournaments) < 1) throw new \Exception("Нет турниров");
            return response()->json(['message' => 'Турниры найдены', 'tournaments' => $tournaments, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function createTournamentByAdmin(Request $request) {
        $validator = Validator::make($request->all(), [
            'new_tournament' => 'required|array',
            'options' => 'required|array'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $newTournament = $request->get('new_tournament');
        $options = $request->get('options');
        $game_id = $newTournament['game_id'];

        try {
            $game = GamesController::getGameById($game_id);
            $gameSlug = $game->slug;
            if (!self::createNewTournament($newTournament, $options, $gameSlug)) throw new \Exception("Ошибка при создании турнира");
            return response()->json(['message' => 'Турнир создан', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function removeTournament(Request $request) {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|integer',
            'game_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $tournamentId = $request->get('tournament_id');
        $gameId = $request->get('game_id');
        try {
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $tableForGame = $gameSlug.'_tournaments_info';

            Tournaments::where('id', $tournamentId)->delete();
            DB::table($tableForGame)->where('tournament_id', $tournamentId)->delete();
            return response()->json(['message' => 'Турнир успешно удален', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function saveAutoTournOptions(Request $request) {
        $validator = Validator::make($request->all(), [
            'options' => 'required|array',
            'game_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $options = $request->get('options');
        $gameId = $request->get('game_id');
        try {
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $optionsTable = $gameSlug.'_auto_tourn_options';

            switch ($gameId) {
                case '1': //pubg
                    foreach ($options['options'] as $option) {
                        $pov = ['tpp', 'fpp'];
                        $option['tournament_options']['pov'] = array_rand($pov, 1);
                        if (!DB::table($optionsTable)->updateOrInsert(['mode' => $option['tournament_options']['mode']], $option['tournament_options'])) throw new \Exception("Ошибка записи в БД");
                        $optionId = DB::table($optionsTable)->select('id')->where('mode', $option['tournament_options']['mode'])->first();
                        if ($optionId === null) throw new \Exception("Ошибка получения option_id");
                        $option['schedule_options']['option_id'] = $optionId;
                        $option['schedule_options']['game_id'] = $gameId;
                        if (!DB::table('auto_options_schedule')->updateOrInsert(['option_id' => $optionId, 'game_id' => $gameId], $option['schedule_options'])) throw new \Exception("Ошибка записи очереди в БД");
                    }
                    break;
                default:
                    throw new \Exception('Игра не найдена');
                    break;
            }
            return response()->json(['message' => 'Настройки успешно сохранены', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public static function createAutoTournamentsBySchedule() {
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
            $map = DB::table('game_maps')->select('name')->where('game_id', $game_id)->inRandomOrder()->first();

            $titleDict = array('азартный', 'безжалостный', 'безумный', 'беспощадный', 'бурный', 'грандиозный', 'грозный', 'дикий', 'дремучий', 'дьявольский', 'жесточайший',
            'матерый', 'незабываемый', 'неистовый', 'пьянящий', 'райский', 'смертельный', 'триумфальный', 'убийственный', 'ужасающий',
            'экстремальный', 'яркий', 'яростный');

            $title = array_rand($titleDict, 1) . ' ' . $map;
            $img = DB::table('tournaments_covers')->select('name')->where('game_id', $game_id)->inRandomOrder()->first();

            $newTournament = [
                'title' => $title,
                'game_id' => $game_id,
                'tickets' => $tickets,
                'img' => $img,
                'start_time' => $start_time,
                'region' => $region
            ];

            $options['lobby_pass'] = substr(str_shuffle('123456789abcdefghijklmnpqrstuvwxyz'), 0, 8);
            $options['map'] = $map;

            if (!self::createNewTournament($newTournament, $options, $gameSlug)) {
                return false;
            };
        }
        return true;
    }

    public function editTournamentInfo(Request $request) {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|integer',
            'game_id' => 'required|integer',
            'tournament_common_info' => 'array',
            'tournament_info_by_game' => 'array'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $tournamentId = $request->get('tournament_id');
        $gameId = $request->get('game_id');
        if ($request->has('tournament_common_info')) $tournamentCommonInfo = $request->get('tournament_common_info');
        if ($request->has('tournament_info_by_game')) $tournamentInfoByGame = $request->get('tournament_info_by_game');

        try {
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $tableForGame = $gameSlug.'_tournaments_info';
            $tournamentInfoByGame['tournament_id'] = $tournamentId;

            if (isset($tournamentCommonInfo)) {
                $tournament = Tournaments::where('id', $tournamentId);
                $tournament->update($tournamentCommonInfo);
            }

            if (isset($tournamentInfoByGame)) DB::table($tableForGame)->where('tournament_id', $tournamentId)->update($tournamentInfoByGame);

            return response()->json(['message' => 'Изменения успешно сохранены', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function getLobbyInfo(Request $request) {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $tournamentId = $request->get('tournament_id');
        try {
            $lobbyInfo = Tournaments::select('lobby_id', 'lobby_pass')->where('id', $tournamentId)->makeVisible(['lobby_id', 'lobby_pass']);
            $lobbyInfo = ['lobby_id' => $lobbyInfo->lobby_id, 'lobby_pass' => $lobbyInfo->lobby_pass];
            return response()->json(['message' => 'Данные для лобби получены', 'lobby_info' => $lobbyInfo, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function saveResult(Request $request) {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|integer',
            'tournament_results' => 'required|array'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $tournamentId = $request->get('tournament_id');
        $tournamentResults = $request->get('tournament_results');

        try {
            $tournament = Tournaments::where('id', $tournamentId)->firstOrFail();
            // Считаем цену за участие в турнире
            $ticketsForTournament = intval($tournament->tickets);
            $priceForTicket = DB::table('ticket_prices')->select('price')->where('count', 1)->first();
            $totalPrice = intval($tournament->tickets) * intval($priceForTicket->price);

            // Находим таблицу с данными турнира для нужной игры
            $gameId = $tournament->game_id;
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $infoTable = $gameSlug.'_tournaments_info';

            $tournamentInfo = DB::table($infoTable)->where('tournament_id', $tournamentId)->first();
            $players = $tournamentInfo->current_players;
            $winners = $tournamentInfo->winners;

            // Считаем призовой фонд
            $prizeFund = ($players * $totalPrice) * 0.8;

            // Считаем выплату для каждого победителя
            $payment = $prizeFund/$winners;

            foreach ($tournamentResults as $result) {

                $userId = intval($result['user_id']);
                $placement = intval($result['placement']);
                $award = 0;
                $rating = 0;
                if ($placement <= $winners) {
                    $award = $payment;
                    $rating += 75;
                }

                $mvp = intval($result['mvp']);

                DB::table('tournaments_and_users')->where('tournament_id', $tournamentId)->where('user_id', $userId)->update([
                    'placement' => $placement,
                    'award' => $award,
                    'total_rating' => $rating,
                    'mvp' => $mvp
                ]);

                $user = User::where('id', $userId)->firstOrFail();
                $user->coins += $award;
                $user->save();

                if ($gameId == 1) {
                    $kills = intval($result['kills']);
                    $deaths = intval($result['deaths']);
                    if ($kills > 0) {
                        $rating += 25 * $kills;
                    }
                    $gameInfo = DB::table('pubg_info')->select('matches', 'kills', 'deaths', 'rating')->where('user_id', $userId)->first();
                    DB::table('pubg_info')->where('user_id', $userId)->update([
                        'matches' => $gameInfo->matches + 1,
                        'kills' => $gameInfo->kills + $kills,
                        'deaths' => $gameInfo->deaths + $deaths,
                        'rating' => $gameInfo->rating + $rating
                    ]);

                    $statistic = [
                        'user_id' => $userId,
                        'earnings' => $award,
                        'kills' => $kills,
                        'placements' => $placement,
                        'tournaments' => 1
                    ];
                }
                StatisticController::saveStatistic($statistic, $gameId);
            }
            $tournament->ended = 1;
            $tournament->save();
            return response()->json(['message' => 'Результаты турнира успешно сохранены', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'line' => $e->getLine(), 'status' => 'error'], 400);
        }
    }

    public function getAllStreams() {
        try {
            $streams = Tournaments::select('title', 'stream')->where('ended', 0)->get();
            if ($streams->isEmpty()) throw new \Exception('Стримы не найдены');
            return response()->json(['message' => 'Список стримов получен', 'streams' => $streams, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function getAllTournamentsForAdmin(Request $request) {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'integer',
            'tournament_title' => 'string',
            'start_date' => 'date',
            'start_time' => 'string'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        try {
            $tournaments = DB::table('tournaments');

            if ($request->has('tournament_id')) {
                $tournamentId = $request->get('tournament_id');
                $tournaments = $tournaments->where('id', $tournamentId);
            }
            if ($request->has('tournament_title')) {
                $tournamentTitle = $request->get('tournament_title');
                $tournaments = $tournaments->where('title', 'like', $tournamentTitle);
            }

            if ($request->has('start_date')) {
                $startDate = $request->get('start_date');
                $tournaments = $tournaments->whereDate('start_time', $startDate);
            }

            if ($request->has('start_time')) {
                $startTime = $request->get('start_time');
                $tournaments = $tournaments->whereTime('start_time', $startTime.':00');
            }

            $tournaments = $tournaments->get();

            if ($tournaments->isEmpty()) throw new \Exception("Турниры не найдены");
            return response()->json(['message' => 'Турниры получены', 'status' => 'success', 'tournaments' => $tournaments]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function getTournamentsOptionForAdmin(Request $request) {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $tournamentId = $request->get('tournament_id');
        try {
            $gameId = Tournaments::select('game_id')->where('id', $tournamentId)->firstOrFail()->game_id;
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $tableForGame = $gameSlug.'_tournaments_info';
            $tournamentInfo = DB::table('tournaments')
                ->join($tableForGame, $tableForGame.'.tournament_id', '=', 'tournaments.id')
                ->select('tournaments.*', $tableForGame.'.*')
                ->where('tournaments.id', $tournamentId)
                ->get();
            return response()->json(['message' => 'Данные для турнира получены', 'status' => 'success', 'tournamentInfo' => $tournamentInfo]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }

    }

    public function getParticipants(Request $request) {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $tournamentId = $request->get('tournament_id');
        try {
            $users = DB::table('tournaments_and_users')->join('users', 'users.id', '=', 'tournaments_and_users.user_id')->select('users.id', 'users.nickname')->where('tournament_id', $tournamentId)->get();
            if ($users->isEmpty()) throw new \Exception("Участники не найдены");
            return response()->json(['message' => 'Участники турнира найдены', 'status' => 'success', 'users' => $users]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function getTournamentInfo(Request $request) {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|integer',
            'tournament_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        try {
            $game_id = $request->get('game_id');
            $tournamentId = $request->get('tournament_id');
            $game = GamesController::getGameById($game_id);
            $gameSlug = $game->slug;
            $tableForGame = $gameSlug.'_tournaments_info';
            $tournament = DB::table('tournaments')
                ->join($tableForGame, $tableForGame.'.tournament_id', '=', 'tournaments.id')
                ->leftJoin('tournaments_and_users', function ($join) {
                    $userId = auth('api')->user()->id;
                    $join->on('tournaments_and_users.tournament_id', '=', 'tournaments.id')
                    ->where('tournaments_and_users.user_id', $userId);
                })
                ->select('tournaments.*', $tableForGame.'.*', 'tournaments_and_users.user_id as participation')
                ->where('tournaments.id', $tournamentId)
                ->get();

            if ($tournament->isEmpty()) throw new \Exception('Турнир не найден');
            return response()->json(['message' => 'Турниры найдены', 'tournament' => $tournament, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }



    private static function createNewTournament(array $newTournament, array $newTournamentOptions, string $gameSlug) {
        $tournamentsInfoTable = $gameSlug.'_tournaments_info';

        $tournament_id = DB::table('tournaments')->insertGetId($newTournament);
        $newTournamentOptions['tournament_id'] = $tournament_id;
        if (DB::table($tournamentsInfoTable)->insert($newTournamentOptions)) {
            return true;
        }
        return false;
    }
}
