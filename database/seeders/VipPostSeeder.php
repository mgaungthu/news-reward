<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;

class VipPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 🧍 Normal post (free)
        Post::create([
            'user_id' => 1, // adjust based on your existing users
            'title' => 'Breaking News: Public Access',
            'excerpt' => 'This post is available for everyone.',
            'body' => 'This is a regular non-VIP post for all users.',
            'status' => 'published',
            'feature_image' => 'https://via.placeholder.com/600x300.png?text=Normal+Post',
            'category_id' => 1,
            'tags' => 'news,free',
            'read_more_url' => 'https://example.com/public-news',
            'is_vip' => false,
            'required_points' => 0,
        ]);

        // 💎 VIP post (requires points)
        Post::create([
            'user_id' => 1,
            'title' => 'Exclusive: VIP Insider Report',
            'excerpt' => 'Only users with enough points can access this.',
            'body' => 'This VIP article contains premium information for point holders.',
            'status' => 'published',
            'feature_image' => 'https://via.placeholder.com/600x300.png?text=VIP+Post',
            'category_id' => 1,
            'tags' => 'vip,exclusive',
            'read_more_url' => 'https://example.com/vip-report',
            'is_vip' => true,
            'required_points' => 50,
        ]);
    }
}
