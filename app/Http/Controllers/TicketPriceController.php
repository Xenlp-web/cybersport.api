<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketPrice;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;

class TicketPriceController extends Controller
{
    public function getPrice(Request $request) {
        $validator = Validator::make($request->all(), [
            'count' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        try {
            $price = TicketPrice::all();
            if ($request->get('count')) {
                $count = $request->get('count');
                $price = TicketPrice::where('count', $count)->first();
            }
            if ($price === null || count($price) < 1) throw new \Exception("Цена не найдена");
            return response()->json(['message' => 'Цена найдена', 'price' => $price, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }
}
