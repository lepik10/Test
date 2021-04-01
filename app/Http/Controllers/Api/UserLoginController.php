<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->input('email') && $request->input('password')) {
            $email = $request->input('email');
            $password = $request->input('password');
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                $user = Auth::user();
                return response()->json(['api_token' => $user->api_token]);
            }
        }

        return response()->json('Wrong Access', 401);
    }
}
