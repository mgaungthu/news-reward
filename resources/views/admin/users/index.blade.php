@extends('layouts.admin')

@section('content')
<div class="p-6">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">Users</h1>
    <!-- <a href="{{ route('users.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">
       + Add New User
    </a> -->
  </div>

  @if(session('success'))
    <div class="mb-4 p-4 border-l-4 border-green-500 bg-green-100 rounded-md">
        <p class="text-green-700 font-medium">{{ session('success') }}</p>
    </div>
  @endif

  <table class="min-w-full bg-white border">
    <thead>
      <tr class="bg-gray-100 text-left">
        <th class="py-3 px-4 border-b">Name</th>
        <th class="py-3 px-4 border-b">Email</th>
        <th class="py-3 px-4 border-b text-center">Points</th>
        <th class="py-3 px-4 border-b">Created</th>
        <th class="py-3 px-4 border-b text-right">Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($users as $user)
      <tr class="hover:bg-gray-50">
        <td class="py-3 px-4 border-b">{{ $user->name }}</td>
        <td class="py-3 px-4 border-b">{{ $user->email }}</td>
        <td class="py-3 px-4 border-b text-center text-yellow-600 font-semibold">
          {{ $user->points }}
        </td>
        <td class="py-3 px-4 border-b">{{ $user->created_at->diffForHumans() }}</td>
        <td class="py-3 px-4 border-b text-right space-x-2">
          <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:underline">Edit</a>
          <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
            @csrf @method('DELETE')
            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete this user?')">Delete</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection