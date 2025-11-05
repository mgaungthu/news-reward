<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    // GET /api/settings
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        if (!empty($settings['banner_image'])) {
            $settings['banner_image'] = asset('storage/' . $settings['banner_image']);
        }

        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }

    // PUT /api/settings
    public function update(Request $request)
    {
        foreach ($request->except(['_token']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully!'
        ]);
    }
}