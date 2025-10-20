<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\UserPostClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostClaimController extends Controller
{
    public function claim(Post $post)
    {
        $user = Auth::user();

        $claim = UserPostClaim::updateOrCreate(
            ['user_id' => $user->id, 'post_id' => $post->id],
            ['status' => 'claimed', 'claimed_at' => now()]
        );

        return response()->json([
            'message' => 'Reward claimed successfully!',
            'post_id' => $post->id,
            'status' => $claim->status,
            'claimed_at' => $claim->claimed_at,
        ]);
    }
}