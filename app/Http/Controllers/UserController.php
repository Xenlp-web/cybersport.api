<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tournaments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmationCode;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    protected function failedValidation($validator) {
        throw new ValidationException($validator);
    }

    public function getUserInfo(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $userId = $request->get('user_id');
        try {
            $user = User::findOrFail($userId);
            return response()->json(['message' => 'Пользователь найден', 'user' => $user, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function getCurrentUserInfo() {
        try {
            $user = Auth::user()->makeVisible(['coins', 'coins_bonus', 'tickets', 'referal_code', 'coins_from_referals', 'confirmed_email']);
            return response()->json(['message' => 'Информация получена', 'user' => $user, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function changeUserInfoByAdmin(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_info' => 'required|array',
            'user_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $userInfo = $request->get('user_info');
        $userId = $request->get('user_id');
        try {
            User::where('id', $userId)->update($userInfo);
            return response()->json(['message' => 'Информация пользователя успешно изменена', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function joinTournament(Request $request) {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|integer',
            'game_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $userId = Auth::id();
        $tournamentId = $request->get('tournament_id');
        $gameId = $request->get('game_id');
        try {
            $tournamentTickets = Tournaments::select('tickets')->where('id', $tournamentId)->where('ended', '0')->firstOrFail();
            $user = Auth::user();

            if ($user->tickets < $tournamentTickets->tickets) throw new \Exception("Недостаточно билетов");

            DB::table('tournaments_and_users')->insert(['user_id' => $userId, 'tournament_id' => $tournamentId]);
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $tournInfoTable = $gameSlug.'_tournaments_info';

            $players = DB::table($tournInfoTable)->select('current_players', 'max_players')->where('tournament_id', $tournamentId)->first();
            if ($players->current_players >= $players->max_players) throw new \Exception("Недостаточно мест");

            DB::table($tournInfoTable)->where('tournament_id', $tournamentId)->update(['current_players' => $players->current_players + 1]);

            $user->tickets = $user->tickets - $tournamentTickets->tickets;
            $user->save();
            return response()->json(['message' => 'Запись на турнир прошла успешно', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function cancelTournamentParticipation(Request $request) {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|integer',
            'game_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $userId = Auth::id();
        $tournamentId = $request->get('tournament_id');
        $gameId = $request->get('game_id');
        try {
            DB::table('tournaments_and_users')->where('user_id', $userId)->where('tournament_id', $tournamentId)->delete();
            $tournamentTickets = Tournaments::select('tickets')->where('id', $tournamentId)->where('ended', 0)->firstOrFail();
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $tournInfoTable = $gameSlug.'_tournaments_info';
            $players = DB::table($tournInfoTable)->select('current_players')->where('tournament_id', $tournamentId)->first();
            DB::table($tournInfoTable)->where('tournament_id', $tournamentId)->update(['current_players' => $players->current_players - 1]);
            $user = Auth::user();
            $user->tickets = $user->tickets + $tournamentTickets->tickets;
            $user->save();
            return response()->json(['message' => 'Участие в турнире отменено', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function addGameInfo(Request $request) {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|integer',
            'game_info' => 'required|array'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $userId = Auth::id();
        $gameId = $request->get('game_id');
        $gameInfo = $request->get('game_info');
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
        $validator = Validator::make($request->all(), [
            'user_info' => 'required|array'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $user = Auth::id();

        try {
            User::find($user)->update($request->get('user_info'));
            return response()->json(['message' => 'Информация успешно обновлена', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function sendNewEmailConfirmationCode() {
        try {
            $user = Auth::user();
            $auth = new AuthController;
            $newConfirmationCode = $auth->genMailConfirmationCode();
            $user->email_confirmation_code = $newConfirmationCode;
            $user->save();
            Mail::to($user)->send(new ConfirmationCode($user->email_confirmation_code));
            return response()->json(['message' => 'Новый код подтверждения отправлен', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function confirmEmail(Request $request) {
        $validator = Validator::make($request->all(), [
            'confirmation_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $user = Auth::user();
        $confirmationCode = strtolower($request->get('confirmation_code'));
        try {
            if ($confirmationCode != $user->email_confirmation_code) throw new \Exception("Неверный код подтверждения");
            $user->confirmed_email = '1';
            $user->save();
            return response()->json(['message' => 'Почта успешно подтверждена', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function uploadAvatar(Request $request) {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpg,png|max:2048'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $userId = Auth::id();
        $file = $request->file('file');

        try {
            $file->storeAs('public/avatars', $userId.'.'.$file->extension());
            return response()->json(['message' => 'Аватар успешно загружен', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function getRating(Request $request) {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|integer',
            'user_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $gameId = $request->get('game_id');
        $userId = $request->get('user_id');

        try {
            $game = GamesController::getGameById($gameId);
            $gameSlug = $game->slug;
            $infoTable = $gameSlug.'_info';
            $rating = DB::table($infoTable)->select('rating')->where('user_id', $userId)->first();
            return response()->json(['message' => 'Рейтинг получен', 'rating' => $rating->rating, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }

    public function useReferalCode(Request $request) {
        $validator = Validator::make($request->all(), [
            'referal_code' => 'required'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $referalCode = $request->get('referal_code');

        try {
            $user = User::where('referal_code', $referalCode)->firstOrFail();
            $user->tickets += 1;
            $user->save();
            $transfer = new TransferController;
            $requestForTransfer = new Request();
            $requestForTransfer->setMethod('POST');
            $requestForTransfer->replace(['transfer_type' => 'income_tickets_referal', 'amount' => 1, 'user_id' => $user->id]);
            $transfer->saveNewTransfer($requestForTransfer);
            return response()->json(['message' => 'Код активирован', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }
}
