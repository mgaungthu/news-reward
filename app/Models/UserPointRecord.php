<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPointRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points_change',
        'source',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
