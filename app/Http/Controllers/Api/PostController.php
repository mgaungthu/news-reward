<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\UserPostClaim;
use App\Repositories\PostRepository;
use App\Services\PostService;
use App\Services\PurchaseService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService,
        private PurchaseService $purchaseService,
        private PostRepository $posts
    ) {}

    // Public non-VIP list
    public function index()
    {
        return response()->json($this->postService->listPublic());
    }

    // VIP list (public listing is OK; details are still protected by show())
    public function vipPosts()
    {
        return response()->json($this->postService->listVip());
    }

    // Show a post (policy-enforced)
    public function show(Request $request, $id)
    {
        $user = Auth::guard('api')->user(); // may be null for guests

        try {
            $post = $this->postService->show($user, (int) $id);
            return response()->json($post);
        } catch (AuthorizationException $e) {
            // If not authorized and it's VIP, return informative payload
            $post = $this->posts->findWithRelationsOrFail((int) $id);

            return response()->json([
                'message' => 'You need to buy this post before reading.',
                'required_points' => $post->required_points,
                'user_points' => $user?->points,
            ], 403);
        }
    }

    // Buy a VIP post (must be authenticated—protect via route middleware)
    public function buy(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['message' => 'Login required to buy VIP post.'], 401);
        }

        $post = Post::findOrFail((int) $id);

        try {
            $result = $this->purchaseService->buyVipPost($user, $post);
            return response()->json($result);
        } catch (AuthorizationException $e) {
            $status = str_contains($e->getMessage(), 'Not enough points') ? 403 : 400;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }


    public function purchasedVipPosts()
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Login required.'], 401);
        }

        $vipPosts = $user->vipPurchases()->with('author')->get();

        return response()->json($vipPosts);
    }


    public function resetUserClaims()
    {
        // Cooldown: allow reset only once every 3 minutes
        $userId = Auth::guard('api')->id();
        $cacheKey = "reset_claim_cooldown_{$userId}";

        // If cache key exists, user must wait
        if (cache()->has($cacheKey)) {
            $secondsLeft = cache()->get($cacheKey) - time();
            $minutesLeft = ceil($secondsLeft / 60);

            return response()->json([
                'message' => "Please wait {$minutesLeft} minute(s) before resetting again."
            ], 429);
        }

        // Set cooldown (store next allowed timestamp)
        cache()->put($cacheKey, time() + (3 * 60), 3 * 60);

        // Allow only mobile devices to reset claims
        

        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Login required.'], 401);
        }

        // Option B: Reset claim status instead of deleting
        UserPostClaim::where('user_id', $user->id)
            ->update([
                'status' => 'pending',
                'claimed_at' => null,
            ]);

        return response()->json([
            'message' => 'Your post claims have been reset successfully.'
        ]);
    }
}