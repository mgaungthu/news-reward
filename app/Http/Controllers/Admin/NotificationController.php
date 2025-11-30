<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected $expo;

    public function __construct()
    {
    }

    /**
     * Display all users with Expo push tokens (for Blade).
     */
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'expo_push_token', 'email')
            ->whereNotNull('expo_push_token')
            ->get();

        return view('admin.notifications.index', compact('users'));
    }

    /**
     * Send a push notification to a specific user.
     */
    public function sendToUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'user_ids' => 'required|array',
        'title' => 'required|string',
        'body' => 'required|string',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $users = User::whereIn('id', $request->user_ids)
            ->whereNotNull('expo_push_token')
            ->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'No users with valid Expo push tokens found.');
        }

        $tokens = $users->pluck('expo_push_token')->toArray();

        $message = [
            'to' => $tokens,
            'sound' => 'default',
            'title' => $request->title,
            'body' => $request->body,
            'data' => (object)[],
        ];

        $response = Http::post('https://exp.host/--/api/v2/push/send', $message);
        if (!$response->successful()) {
            return redirect()->back()->with('error', 'Notification failed: ' . $response->body());
        }

        return redirect()->back()->with('success', 'Notification sent successfully to selected users!');
    }
}