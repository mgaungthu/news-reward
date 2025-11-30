<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\UserPointRecord;

class PointHelper
{
    public static function log(User $user, $amount, $source)
    {
        UserPointRecord::create([
            'user_id' => $user->id,
            'points_change' => $amount,
            'source' => $source,
        ]);
    }
}