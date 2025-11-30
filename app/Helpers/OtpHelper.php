<?php

namespace App\Helpers;

class OtpHelper
{
    public static function generate($user, $fieldCode, $fieldExpire)
    {
        $otp = rand(100000, 999999);

        $user->{$fieldCode} = $otp;
        $user->{$fieldExpire} = now()->addMinutes(10);
        $user->save();

        return $otp;
    }
}