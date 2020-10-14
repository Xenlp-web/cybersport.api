<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tournaments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUserInfo(Request $request) {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $email = $request->get('email');
        try {
            $user = User::where('email', $email)->first();
            if ($user === null) throw new \Exception('Пользователь не найден');
            return response()->json(['message' => 'Пользователь найден', 'user' => $user, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function changeUserInfoByAdmin(Request $request) {
        (array) $userInfo = $request->get('user_info');
        try {
            User::where('id', $userInfo['id'])->update($userInfo);
            return response()->json(['message' => 'Информация пользователя успешно изменена', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function joinTournament(Request $request) {
        (int) $userId = Auth::id();
        (int) $tournamentId = $request->get('tournament_id');
        (int) $gameId = $request->get('game_id');
        try {
            $tournamentTickets = Tournaments::select('tickets')->where('id', $tournamentId)->where('ended', '0')->firstOrFail();
            $user = Auth::user();

            if ($user->tickets < $tournamentTickets) throw new \Exception("Недостаточно билетов");

            DB::table('tournaments_and_users')->insert(['user_id' => $userId, 'tournament_id' => $tournamentId]);
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $tournInfoTable = $gameSlug.'_tournaments_info';

            $players = DB::table($tournInfoTable)->select('current_players', 'max_players')->where('tournament_id', $tournamentId)->firstOrFail();
            if ($players->current_players >= $players->max_players) throw new \Exception("Недостаточно мест");

            DB::table($tournInfoTable)->where('tournament_id', $tournamentId)->update(['current_players' => $players->current_players + 1]);

            $user->tickets = $user->tickets - $tournamentTickets;
            $user->save();
            return response()->json(['message' => 'Запись на турнир прошла успешно', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function cancelTournamentParticipation(Request $request) {
        (int) $userId = Auth::id();
        (int) $tournamentId = $request->get('tournament_id');
        (int) $gameId = $request->get('game_id');
        try {
            DB::table('tournaments_and_users')->where('user_id', $userId)->where('tournament_id', $tournamentId)->delete();
            $tournamentTickets = Tournaments::select('tickets')->where('id', $tournamentId)->where('ended', 0)->firstOrFail();
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $tournInfoTable = $gameSlug.'_tournaments_info';
            $players = DB::table($tournInfoTable)->pluck('current_players')->where('tournament_id', $tournamentId);
            DB::table($tournInfoTable)->where('tournament_id', $tournamentId)->update(['current_players' => $players->current_players - 1]);
            $user = Auth::user();
            $user->tickets = $user->tickets + $tournamentTickets;
            $user->save();
            return response()->json(['message' => 'Участие в турнире отменено', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function addGameInfo(Request $request) {
        (int) $userId = Auth::id();
        (int) $gameId = $request->get('game_id');
        (array) $gameInfo = $request->get('game_info');
        $gameInfo['user_id'] = $userId;
        try {
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $tableName = $gameSlug . '_info';
            DB::table($tableName)->updateOrInsert(['user_id' => $userId], $gameInfo);
            return response()->json(['message' => 'Информация успешно добавлена', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function changeUserInfo(Request $request) {
        $user = Auth::user();
        try {
            $user->update($request->get('user_info'));
            return response()->json(['message' => 'Информация успешно обновлена', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }
}
