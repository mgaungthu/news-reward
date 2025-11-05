@extends('layouts.admin')

@section('content')
<div class="p-6">
  <h1 class="text-3xl font-semibold text-gray-800">Welcome, Admin 👋</h1>
  <p class="text-gray-500 mt-2">Manage your posts and users below.</p>

  <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('posts.index') }}" class="block bg-white shadow rounded-lg p-6 text-center hover:shadow-lg transition">
      <h2 class="text-xl font-semibold text-blue-600">Posts</h2>
      <p class="text-gray-500">Create, edit, and publish news articles.</p>
    </a>
    <a href="{{ route('users.index') }}" class="block bg-white shadow rounded-lg p-6 text-center hover:shadow-lg transition">
      <h2 class="text-xl font-semibold text-blue-600">Users</h2>
      <p class="text-gray-500">View registered users and their points.</p>
    </a>
    <a href="{{ route('settings.index') }}" class="bg-white shadow rounded-lg p-6 text-center">
      <h2 class="text-xl font-semibold text-blue-600">Setting</h2>
      <p class="text-gray-500">Manage Ad unit and Banner image.</p>
    </a>
  </div>
</div>
@endsection