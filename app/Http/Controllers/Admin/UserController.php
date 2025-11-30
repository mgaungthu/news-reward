<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;


class UserController extends Controller
{
    public function index()
    {
     $query = User::with(['postClaims', 'referrals'])
    ->where('is_admin', 0);

    if (request('email')) {
        $query->where('email', 'like', '%' . request('email') . '%');
    }

    $users = $query
    ->orderBy('updated_at', 'desc')
    ->paginate(15);
    $totalPoints = User::where('is_admin', 0)->sum('points');
    $totalReferralRewards = User::where('is_admin', 0)->sum('referral_rewarded');
    $averagePoints = User::where('is_admin', 0)->avg('points');
    $averageMaxPoints = User::where('is_admin', 0)->max('points');
    $averagePointUsers = User::where('is_admin', 0)
        ->orderByRaw('ABS(points - ?)', [$averagePoints])
        ->get();

   

    // Apply filter buttons with pagination support
    if (request('filter') === 'avg') {
        $collection = $averagePointUsers;
    } 

    if (isset($collection)) {
        $collection = $collection->sortByDesc('points')->values();
    }

    if (isset($collection)) {
        $page = request()->get('page', 1);
        $perPage = 15;
        $currentItems = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        $users = new LengthAwarePaginator(
            $currentItems,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    return view('admin.users.index', compact(
        'users', 
        'totalPoints', 
        'totalReferralRewards', 
        'averagePoints', 
        'averageMaxPoints',
        'averagePointUsers',
    ));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $user->load([
            'referrals' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'pointRecords' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
        ]);

        return view('admin.users.show', [
            'user' => $user,
            'currentPoints' => $user->points,
            'referralList' => $user->referrals,
            'pointRecords' => $user->pointRecords()->orderBy('created_at', 'desc')->paginate(15),
        ]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'deduct_points' => 'nullable|integer|min:0',
        ]);


        // handle deduction
        if (!empty($validated['deduct_points']) && $validated['deduct_points'] > 0) {
            $user->points = max(0, $user->points - $validated['deduct_points']);
            $user->save();
        }

        return redirect()->route('users.index')->with('success', 'User point deduction is successfully!');
    }

    public function destroy(User $user)
    {
        \Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/delete/delete.log'),
        ])->info('User deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
            'points'    => $user->points,
            'deleted_at' => now()->toDateTimeString(),
            'action_by' => auth()->check() ? auth()->id() : null,
        ]);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
    
    public function savePushToken(Request $request)
    {
        $request->validate([
            'expo_push_token' => 'required|string',
        ]);

        $user = Auth::user();
        $user->expo_push_token = $request->expo_push_token;
        $user->save();

        return response()->json(['message' => 'Expo push token saved successfully']);
    }
}