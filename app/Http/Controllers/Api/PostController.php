<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $posts = Post::with(['author', 'rewards', 'userClaims'])
        ->where('status', 'published')
        ->latest()
        ->get();
        return response()->json($posts);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = Post::with(['author', 'rewards', 'userClaims'])->where('id', $id)->firstOrFail();
        return response()->json($post);
    }

}
