<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;

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
        $users = User::select('id', 'name', 'expo_push_token')
            ->whereNotNull('expo_push_token')
            ->get();

        return view('admin.notifications.index', compact('users'));
    }

    /**
     * Send a push notification to a specific user.
     */
    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $users = User::whereIn('id', $request->user_ids)
            ->whereNotNull('expo_push_token')
            ->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'No users with valid Expo push tokens found.');
        }

        // Build array of message payloads
        $messages = $users->map(function ($user) use ($request) {
            return [
                'to' => $user->expo_push_token,
                'sound' => 'default',
                'title' => $request->title,
                'body' => $request->body,
                'data' => (object)[],
            ];
        })->toArray();

        // Send to Expo in one request
        $response = Http::post('https://exp.host/--/api/v2/push/send', $messages);

        if ($response->successful()) {
            return redirect()->back()->with('success', 'Notification sent successfully to selected users!');
        }

        return redirect()->back()->with('error', 'Failed to send notifications: ' . $response->body());
    }
}