<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function getCurrentUserInformation($email) {
        return User::where('email', $email)->first();
    }
}
