<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['token' => 'required|string|max:500']);

        Auth::user()->update(['fcm_token' => $request->token]);

        return response()->json(['ok' => true]);
    }
}
