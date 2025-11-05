@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold">Application Settings</h2>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md">Back</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.update', 1) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h4 class="mb-3 font-bold text-lg">General Settings</h4>
            <label class="block text-sm font-medium text-gray-700 mb-2">Points Per Claim</label>
            <input type="text" name="points_per_claim" value="{{ $settings['points_per_claim'] ?? '' }}" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1" />
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h4 class="mb-3 font-bold text-lg">Advertisement Settings</h4>

            <!-- Banner -->
            <label class="block text-sm font-medium text-gray-700 mb-2">Banner Ad Unit ID</label>
            <input type="text" name="ad_banner_id_android" value="{{ $settings['ad_banner_id_android'] ?? '' }}" placeholder="/6499/example/banner (Android)" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1 mb-2" />
            <input type="text" name="ad_banner_id_ios" value="{{ $settings['ad_banner_id_ios'] ?? '' }}" placeholder="/6499/example/banner (iOS)" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1" />

            <!-- Interstitial -->
            <label class="block text-sm font-medium text-gray-700 mt-4 mb-2">Interstitial Ad Unit ID</label>
            <input type="text" name="ad_interstitial_id_android" value="{{ $settings['ad_interstitial_id_android'] ?? '' }}" placeholder="/6499/example/interstitial (Android)" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1 mb-2" />
            <input type="text" name="ad_interstitial_id_ios" value="{{ $settings['ad_interstitial_id_ios'] ?? '' }}" placeholder="/6499/example/interstitial (iOS)" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1" />

            <!-- Reward -->
            <label class="block text-sm font-medium text-gray-700 mt-4 mb-2">Reward Ad Unit ID</label>
            <input type="text" name="ad_reward_id_android" value="{{ $settings['ad_reward_id_android'] ?? '' }}" placeholder="/6499/example/rewarded (Android)" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1 mb-2" />
            <input type="text" name="ad_reward_id_ios" value="{{ $settings['ad_reward_id_ios'] ?? '' }}" placeholder="/6499/example/rewarded (iOS)" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1" />

            <!-- App Open -->
            <label class="block text-sm font-medium text-gray-700 mt-4 mb-2">App Open Ad Unit ID</label>
            <input type="text" name="ad_app_open_id_android" value="{{ $settings['ad_app_open_id_android'] ?? '' }}" placeholder="/6499/example/app-open (Android)" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1 mb-2" />
            <input type="text" name="ad_app_open_id_ios" value="{{ $settings['ad_app_open_id_ios'] ?? '' }}" placeholder="/6499/example/app-open (iOS)" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1" />
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h4 class="mb-3 font-bold text-lg">Google Ads App IDs</h4>
            <label class="block text-sm font-medium text-gray-700 mb-2">Android App ID</label>
            <input type="text" name="android_app_id" value="{{ $settings['android_app_id'] ?? '' }}" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1" />
            <label class="block text-sm font-medium text-gray-700 mt-4 mb-2">iOS App ID</label>
            <input type="text" name="ios_app_id" value="{{ $settings['ios_app_id'] ?? '' }}" class="w-full border-b border-gray-300 focus:border-blue-500 focus:outline-none py-2 px-1" />
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h4 class="mb-3 font-bold text-lg">Banner Image</h4>
            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Banner Image</label>
            <input type="file" name="banner_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            @if(!empty($settings['banner_image']))
                <img src="{{ asset('storage/' . $settings['banner_image']) }}" alt="Banner Image" class="mt-3 w-full rounded-md shadow-md" />
            @endif
        </div>

        <div class="text-center">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg mt-4">Save Settings</button>
        </div>
    </form>
</div>
@endsection