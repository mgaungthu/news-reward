<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PurchaseService
{
    public function buyVipPost(User $user, Post $post): array
    {
        if (!$post) {
            return ['status' => 404, 'message' => 'Post not found'];
        }

        if (!$post->is_vip) {
            // throw new AuthorizationException('This post is not a VIP post.');
            return ['status' => 422, 'message' => 'This post is not a VIP post.'];
        }

        if ($user->vipPurchases()->where('post_id', $post->id)->exists()) {
            // throw new AuthorizationException('You already purchased this post.');
            return ['status' => 409, 'message' => 'You already purchased this VIP post.', 'already_purchased' => true];
        }

        if ($user->points < $post->required_points) {
            // throw new AuthorizationException('Not enough points to buy this post.');
            return ['status' => 422, 'message' => 'Not enough points to purchase this post.'];
        }

        DB::transaction(function () use ($user, $post) {
            // Eloquent version
            $user->decrement('points', $post->required_points);
            $user->vipPurchases()->attach($post->id);
        });

        return [
            'status' => 200,
            'message' => 'VIP post purchased successfully!',
            'remaining_points' => $user->fresh()->points,
        ];
    }
}