<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(10);
        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'required|string',
            'status' => 'required|in:draft,published',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|string',
            'read_more_url' => 'nullable|url|max:255'
        ]);

        if ($request->hasFile('feature_image')) {
            $path = $request->file('feature_image')->store('posts', 'public');
            $data['feature_image'] = $path;
        }

        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($data['title']);
        $data['category_id'] = $request->category_id;
        $data['tags'] = $request->tags;
        $data['read_more_url'] = $request->read_more_url;
        // Check for duplicate slug
        if (Post::where('slug', $data['slug'])->exists()) {
            return back()
                ->withErrors(['slug' => 'A post with this title already exists. Please choose another title.'])
                ->withInput();
        }
        $data['published_at'] = now();

        $post = Post::create($data);

        if ($request->rewards) {
            foreach ($request->rewards as $reward) {
                if (!empty($reward['url'])) {
                    $post->rewards()->create([
                        'title' => $reward['title'] ?? 'Default Title',
                        'type' => $reward['type'] ?? 'default',
                        'url' => $reward['url'],
                    ]);
                }
            }
        }

        return redirect()->route('posts.index')->with('success', 'Post created successfully');
    }

    public function edit(Post $post)
    {
        $categories = Category::all();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required',
            'status' => 'required|in:draft,published',
            'rewards.*.url' => 'nullable|url',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|string',
            'read_more_url' => 'nullable|url|max:255',
        ]);

        if ($request->hasFile('feature_image')) {
            $path = $request->file('feature_image')->store('posts', 'public');
            $validated['feature_image'] = $path;
        }

        $validated['category_id'] = $request->category_id;
        $validated['tags'] = $request->tags;
        $validated['read_more_url'] = $request->read_more_url;

        $post->update($validated);

        $post->rewards()->delete();

        if ($request->rewards) {
            foreach ($request->rewards as $reward) {
                if (!empty($reward['url'])) {
                    $post->rewards()->create([
                        'title' => $reward['title'] ?? 'Default Title',
                        'type' => $reward['type'] ?? 'default',
                        'url' => $reward['url'],
                    ]);
                }
            }
        }


        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return back()->with('success', 'Post deleted.');
    }
}
