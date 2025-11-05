<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {

        if ($request->hasFile('banner_image')) {
            
            $path = $request->file('banner_image')->store('banners', 'public');
            Setting::updateOrCreate(['key' => 'banner_image'], ['value' => $path]);
        }

        foreach ($request->except(['_token', 'banner_image']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
