<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $posts = [
            [
                'user_id' => $user->id,
                'title' => 'Breaking News: Laravel 12 Released',
                'slug' => Str::slug('Breaking News: Laravel 12 Released'),
                'excerpt' => 'Laravel 12 brings a host of new features and performance improvements.',
                'body' => 'Laravel 12 introduces enhanced query performance, route caching improvements, and better support for modern PHP versions.',
                'status' => 'published',
                'feature_image' => 'https://picsum.photos/seed/laravel12/800/400',
                'category_id' => 1,
                'tags' => 'laravel,php,framework',
                'read_more_url' => 'https://laravel.com/docs/12.x',
            ],
            [
                'user_id' => $user->id,
                'title' => 'React Native Adds Support for New Architecture',
                'slug' => Str::slug('React Native Adds Support for New Architecture'),
                'excerpt' => 'React Native’s new architecture aims to boost performance and developer experience.',
                'body' => 'The new architecture uses Fabric and TurboModules, allowing faster UI rendering and smoother animations.',
                'status' => 'published',
                'feature_image' => 'https://picsum.photos/seed/reactnative/800/400',
                'category_id' => 2,
                'tags' => 'react-native,javascript,mobile',
                'read_more_url' => 'https://reactnative.dev',
            ],
        ];

        foreach ($posts as $data) {
            Post::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}