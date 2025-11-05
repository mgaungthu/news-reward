<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    // Anyone can view non-VIP; VIP requires purchase
    public function view(?User $user, Post $post): bool
    {
        if (!$post->is_vip) return true;            // public posts
        if (!$user) return false;                   // login required

        return $user->vipPurchases()->where('post_id', $post->id)->exists();
    }

    // Who can buy a VIP post
    public function buy(User $user, Post $post): bool
    {
        if (!$post->is_vip) return false; // nothing to buy
        if ($user->vipPurchases()->where('post_id', $post->id)->exists()) return false; // already purchased
        return $user->points >= $post->required_points; // has enough points
    }
}