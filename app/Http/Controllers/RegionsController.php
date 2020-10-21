<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Regions;

class RegionsController extends Controller
{
    public function getAll() {
        try {
            $regions = Regions::select('id', 'region')->get();
            if (empty($regions)) throw new \Exception("Регионы не найдены");
            return response()->json(['message' => 'Регионы получены', 'regions' => $regions, 'status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
        }
    }
}
