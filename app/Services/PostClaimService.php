<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Helpers\PointHelper;

class PostClaimService
{
    public function claim(User $user, Post $post)
    {
        // --- Mobile Only Protection ---
        // Block Postman, Browsers, Desktop apps
        $userAgent = request()->header('User-Agent', '');

        // Detect real mobile patterns
        $isMobile = preg_match('/(Android|iPhone|iPad|iPod|CFNetwork|okhttp|Dalvik|Expo)/i', $userAgent);

        // Detect browser/desktop/Postman signatures
        $isBrowser = preg_match('/(Mozilla|Chrome|Safari|Firefox|Edg|Windows NT|Macintosh|Linux|PostmanRuntime)/i', $userAgent);

        // Block any non‑mobile or browser-like agent
        if (!$isMobile || $isBrowser) {
            return [
                'error' => true,
                'message' => 'Only the mobile app can use this feature.'
            ];
        }
        // --- End Mobile Only Protection ---

        // Find existing pending claim
        $claim = $user->postClaims()
            ->where('post_id', $post->id)
            ->where('status', 'pending')
            ->first();

        if ($claim) {
            $claim->status = 'claimed';
            $claim->claimed_at = Carbon::now();
            $claim->save();

            $this->updateUserPoints($user);
            return ['claim' => $claim, 'updated' => true];
        } else {
            $claim = $user->postClaims()
            ->where('post_id', $post->id)
            ->where('status', 'claimed')
            ->first();
            if($claim) {
                return ['claim' => $claim, 'new' => true];
            } else {
             $claim = $user->postClaims()->create([
                'post_id' => $post->id,
                'status' => 'claimed',
                'claimed_at' => null,
            ]);

            $this->updateUserPoints($user);
            return ['claim' => $claim, 'new' => true];
            }
           
        }
    }

    protected function updateUserPoints(User $user)
    {
        $pointsPerClaim = (int) app('points_per_claim');

        // Add normal claim points to the referred user
        $user->points += $pointsPerClaim;
        $user->save();
        PointHelper::log($user, $pointsPerClaim, 'claim');

        // If user was referred by someone, give 0.01 to referrer each claim
        if ($user->referred_by) {
            $referrer = User::where('referral_code', $user->referred_by)->first();

            if ($referrer) {
                // Add points
                $referrer->points += 0.1;
                $referrer->save();
                PointHelper::log($referrer, 0.1, 'referral');
                // --- Referral Counter Logic (decimal tracking) ---

                // Decimal part from referrer points (e.g. 0.1, 0.3, 0.6, 0.0)
                $decimal = fmod($referrer->points, 1);

                // Cache key
                $key = "referral_counter_" . $referrer->id;

                // Save current decimal progress forever
                // Cache::forever($key, $decimal);

                // If decimal is 0 → means referrer reached a full +1 milestone
                if ($decimal == 0) {
                    // Increase referral rewarded count
                    if (isset($referrer->referral_rewarded)) {
                        $referrer->referral_rewarded += 1;
                        $referrer->save();
                        PointHelper::log($referrer, 1, 'referral_bonus');
                    }

                    // Send Expo Push Notification
                    if (!empty($referrer->expo_push_token)) {
                        $messages = [
                            [
                                'to' => $referrer->expo_push_token,
                                'sound' => 'default',
                                'title' => 'Congratulations!',
                                'body' => 'You earned 1 point from referrals.',
                                'data' => (object)[],
                            ]
                        ];

                        Http::post('https://exp.host/--/api/v2/push/send', $messages);
                    }

                    // Reset cached decimal to 0
                    // Cache::forever($key, 0);
                }
            }
        }
    }
}