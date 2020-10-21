<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;

class TransferController extends Controller
{
    protected function failedValidation($validator) {
        throw new ValidationException($validator);
    }

    protected $transferTypes = [
        'income_coins', 'spend_coins', 'income_tickets', 'spend_tickets'
    ];

    public function saveNewTransfer(Request $request) {
        $validator = Validator::make($request->all(), [
            'transfer_type' => 'required|string|in:'.$this->transferTypes,
            'amount' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $transferType = $request->get('transfer_type');
        $amount = $request->get('amount');
        $userId = Auth::id();

        try {
            DB::table('users_transfers')->insert(['user_id' => $userId, 'transfer_type' => $transferType, 'amount' => $amount]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
