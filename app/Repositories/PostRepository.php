<?php

namespace App\Repositories;

use App\Models\Post;

class PostRepository
{
    public function listPublic()
    {
        return Post::with(['author','rewards','userClaims'])
            ->where('status','published')
            ->where('is_vip', false)
            ->latest()
            ->get();
    }

    public function listVip()
    {
        return Post::with(['author','rewards','userClaims'])
            ->where('status','published')
            ->where('is_vip', true)
            ->latest()
            ->get();
    }

    public function findWithRelationsOrFail(int $id): Post
    {
        return Post::with(['author','rewards','userClaims'])->findOrFail($id);
    }
}