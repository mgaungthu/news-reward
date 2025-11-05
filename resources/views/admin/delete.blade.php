@extends('layouts.admin')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-semibold text-red-600 mb-4">Delete Account</h1>

    @if (session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <p class="text-gray-700 mb-6">
        Are you sure you want to permanently delete your account? This action cannot be undone.
    </p>

    <form action="{{ route('admin.account.destroy') }}" method="POST">
        @csrf
        @method('DELETE')

        {{-- Optional password confirmation --}}
        {{-- <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2">Confirm Password</label>
            <input type="password" name="password" id="password" class="border-gray-300 rounded w-full px-3 py-2 focus:outline-none focus:ring focus:ring-red-200" required>
            @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div> --}}

        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            Delete My Account
        </button>
        <a href="{{ route('admin.dashboard') }}" class="ml-3 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
@endsection