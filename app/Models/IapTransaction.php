<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IapTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'product_id',
        'transaction_id',
        'purchase_token',
        'environment',
        'points',
        'status',
        'raw_payload',
        'error_message',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}