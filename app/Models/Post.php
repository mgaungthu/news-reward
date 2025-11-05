<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'slug', 'excerpt', 'body', 'status', 'feature_image', 'category_id', 'tags', 'read_more_url', 'vimeo_url', 'is_vip', 'required_points'];

    protected $appends = ['feature_image_url'];

     public static function boot()
    {
        parent::boot();
        static::creating(function ($post) {
            $post->slug = Str::slug($post->title);
        });
    }

    public function rewards()
    {
        return $this->hasMany(PostReward::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userClaims()
    {
        return $this->hasMany(UserPostClaim::class);
    }

    // For convenience — check if a user has claimed this post
    public function isClaimedBy($userId)
    {
        return $this->userClaims()->where('user_id', $userId)->where('status', 'claimed')->exists();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getTagsArrayAttribute()
    {
        return explode(',', $this->tags ?? '');
    }

    public function purchasers()
    {
        return $this->belongsToMany(User::class, 'vip_post_purchases')
                    ->withTimestamps();
    }

    public function isVip(): bool
    {
        return (bool) $this->is_vip;
    }

    public function getFeatureImageUrlAttribute()
    {
        return $this->feature_image ? asset('storage/' . $this->feature_image) : null;
    }
}
