<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class PostService
{
    public function __construct(private PostRepository $posts) {}

    public function listPublic()
    {
        return $this->posts->listPublic();
    }

    public function listVip()
    {
        return $this->posts->listVip();
    }

    // Handles the “show” behavior with policy
    public function show(?User $user, int $id): Post
    {
        $post = $this->posts->findWithRelationsOrFail($id);

        if (Gate::forUser($user)->denies('view', $post)) {
            // If VIP and not authorized, surface a clear error for the controller to format
            throw new AuthorizationException('VIP purchase required to view this post.');
        }

        return $post;
    }
}