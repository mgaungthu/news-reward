@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-lg mx-auto">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold">Edit User Points</h2>
    <a href="{{ route('users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md">Back</a>
  </div>

  <div class="bg-white p-6 rounded-md shadow-md">
    <form action="{{ route('users.update', $user->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-2">Name</label>
        <input type="text" value="{{ $user->name }}" class="w-full px-3 py-2 border rounded-md bg-gray-100" disabled>
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-2">Email</label>
        <input type="text" value="{{ $user->email }}" class="w-full px-3 py-2 border rounded-md bg-gray-100" disabled>
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-2">Current Points</label>
        <input type="number" value="{{ $user->points }}" class="w-full px-3 py-2 border rounded-md bg-gray-100" disabled>
      </div>

      <div class="mb-6">
        <label class="block text-gray-700 font-medium mb-2">Deduct Points</label>
        <input type="number" name="deduct_points" placeholder="Enter points to deduct"
               class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200" required>
      </div>

      <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md w-full">
        Deduct Points
      </button>
    </form>
  </div>
</div>
@endsection