<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostClaimService;
use Illuminate\Support\Facades\Auth;

class PostClaimController extends Controller
{
    public function __construct(private PostClaimService $claimService) {}

    public function claim(Post $post)
    {
        $user = Auth::user();



        $result = $this->claimService->claim($user, $post);
        $claim = $result['claim'];

                 return response()->json([
                'message' => 'You have started earning points for this post!',
                'post_id' => $post->id,
                'claim' => $claim,
                'user_points' => $user->points,
            ]);

        if (!empty($result['new'])) {
            return response()->json([
                'message' => 'You have started earning points for this post!',
                'post_id' => $post->id,
                'status' => $claim->status,
                'user_points' => $user->points,
            ]);
        }

        if (!empty($result['updated'])) {
            return response()->json([
                'message' => 'You have successfully claimed this post reward!',
                'post_id' => $post->id,
                'status' => $claim->status,
                'claimed_at' => $claim->claimed_at,
                'user_points' => $user->points,
            ]);
        }

        return response()->json([
            'message' => 'Invalid claim request or already claimed.',
        ], 400);
    }
}