<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketPrice;

class TicketPriceController extends Controller
{
    public function getPrice(Request $request) {
        $this->validate($request, [
            'count' => 'required|integer'
        ]);

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
