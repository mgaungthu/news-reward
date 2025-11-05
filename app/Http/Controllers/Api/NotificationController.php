<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class NotificationController extends Controller
{
     public function savePushToken(Request $request)
    {
        $request->validate([
            'expo_push_token' => 'required|string',
        ]);

        $user = Auth::user();

        // Only update if the token is new or different
        if ($user->expo_push_token !== $request->expo_push_token) {
            $user->expo_push_token = $request->expo_push_token;
            $user->save();
        }

        return response()->json(['message' => 'Expo push token saved successfully']);
    }

}
