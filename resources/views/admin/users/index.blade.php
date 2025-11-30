@extends('layouts.admin')

@section('content')
<div class="p-6">
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-2xl font-semibold">
        Users 

        <span style="background:#f3f4f6;" 
              class="ml-3 text-gray-700 text-sm px-3 py-1 rounded-md border">
            Total Points: {{ $totalPoints ?? 0 }}
        </span>

        <span style="background:#eef2ff;" 
              class="ml-3 text-indigo-700 text-sm px-3 py-1 rounded-md border">
            Total Referral Rewards: {{ $totalReferralRewards ?? 0 }}
        </span>

        <span style="background:#fef9c3;" 
              class="ml-3 text-yellow-700 text-sm px-3 py-1 rounded-md border">
            Avg Points: {{ number_format($averageMaxPoints ?? 0, 2) }}
        </span>        
    </h1>
    <form method="GET" action="{{ route('users.index') }}" class="flex items-center space-x-2">
        <input 
            type="text" 
            name="email" 
            value="{{ request('email') }}"
            placeholder="Search by email"
            class="border border-gray-300 rounded-md px-3 py-2"
        >
        <button 
            type="submit"
            style="background:#374151;"
            class="hover:bg-gray-800 text-white px-4 py-2 rounded-md"
        >Search</button>
    </form>
    <!-- <a href="{{ route('users.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">
       + Add New User
    </a> -->
  </div>


    <div class="flex items-center space-x-3 ml-3 mb-3" style="margin-bottom:10px">


            <a style="background:#000" href="{{ route('users.index', ['filter' => 'avg']) }}" 
               class="px-3 py-1 bg-green-500 text-white text-sm rounded-md ml-3">
               Avg Users
            </a>

 
            <a style="background:#b5b5b5" href="{{ route('users.index') }}" 
               class="px-3 py-1 bg-gray-500 text-white text-sm rounded-md ml-3">
                Reset
            </a>

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
        <th class="py-3 px-4 border-b text-center">Ref Rewarded</th>
        <th class="py-3 px-4 border-b">Ref Count</th>
        <th class="py-3 px-4 border-b">Created</th>
        <th class="py-3 px-4 border-b text-right">Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($users as $user)
      <tr class="hover:bg-gray-50">
        <td class="py-3 px-4 border-b">{{ $user->name }}</td>
        <td class="py-3 px-4 border-b">{{ $user->email }}</td>
       

        <td class="py-3 px-4 border-b text-center font-semibold">
            {{ $user->points }}

        </td>
        <td class="py-3 px-4 border-b text-center font-semibold text-purple-600">
            {{ $user->referral_rewarded ?? 0 }}
        </td>
        <td class="py-3 px-4 border-b text-center font-semibold">
            {{ $user->referrals->count() }}
        </td>
        <td class="py-3 px-4 border-b">{{ $user->created_at->diffForHumans() }}</td>
        <td class="py-3 px-4 border-b text-right space-x-2">
          <a href="{{ route('users.show', $user) }}" class="text-green-600 hover:underline">View</a>
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

@section('scripts')
<script>
    setInterval(() => window.location.reload(), 3000);
</script>
@endsection
