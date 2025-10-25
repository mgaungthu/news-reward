<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\App;

class PostClaimService
{
    public function claim(User $user, Post $post)
    {
        // Find or create pending claim
        $claim = $user->postClaims()
            ->where('post_id', $post->id)
            ->first();

        if (!$claim) {
            $claim = $user->postClaims()->create([
                'post_id' => $post->id,
                'status' => 'pending',
                'claimed_at' => null,
            ]);

            $this->updateUserPoints($user);
            return ['claim' => $claim, 'new' => true];
        }

        return ['claim' => $claim, 'new' => false];
    }

    protected function updateUserPoints(User $user)
    {
        $pointsPerClaim = (int) app('points_per_claim') ?? 10;

        $totalPoints = $user->postClaims()
            ->where('status', 'pending')
            ->count() * $pointsPerClaim;

        $user->points = $totalPoints;
        $user->save();
    }
}