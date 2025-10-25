<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'points_per_claim', 'value' => '10'],
            ['key' => 'ad_banner_id', 'value' => 'ca-app-pub-xxx/banner'],
            ['key' => 'ad_interstitial_id', 'value' => 'ca-app-pub-xxx/interstitial'],
            ['key' => 'ad_reward_id', 'value' => 'ca-app-pub-xxx/reward'],
            ['key' => 'ad_app_open_id', 'value' => 'ca-app-pub-xxx/app_open'],
        ];

        foreach ($settings as $data) {
            Setting::updateOrCreate(['key' => $data['key']], ['value' => $data['value']]);
        }
    }
}