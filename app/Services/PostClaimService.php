<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;

class PostClaimService
{
    public function claim(User $user, Post $post)
    {
        // Find existing pending claim
        $claim = $user->postClaims()
            ->where('post_id', $post->id)
            ->where('status', 'pending')
            ->first();

        if ($claim) {
            $claim->status = 'claimed';
            $claim->claimed_at = Carbon::now();
            $claim->save();

            $this->updateUserPoints($user);
            return ['claim' => $claim, 'updated' => true];
        } else {
            $claim = $user->postClaims()
            ->where('post_id', $post->id)
            ->where('status', 'claimed')
            ->first();
            if($claim) {
                return ['claim' => $claim, 'new' => true];
            } else {
             $claim = $user->postClaims()->create([
                'post_id' => $post->id,
                'status' => 'claimed',
                'claimed_at' => null,
            ]);

            $this->updateUserPoints($user);
            return ['claim' => $claim, 'new' => true];
            }
           
        }
    }

    protected function updateUserPoints(User $user)
    {
        $pointsPerClaim = (int) app('points_per_claim');

        
        $user->points += $pointsPerClaim;
        $user->save();
    }
}