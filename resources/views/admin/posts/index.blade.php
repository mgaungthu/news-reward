@extends('layouts.admin')

@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Posts</h1>
    <a href="{{ route('posts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">+ New Post</a>
  </div>

  @if(session('success'))
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
  @endif

  <table class="min-w-full bg-white border">
    <thead>
      <tr class="bg-gray-100 text-left">
        <th class="py-3 px-4 border-b">Title</th>
        <th class="py-3 px-4 border-b">VIP</th>
        <th class="py-3 px-4 border-b">Required Points</th>
        <th class="py-3 px-4 border-b">Status</th>
        <th class="py-3 px-4 border-b">Created</th>
        <th class="py-3 px-4 border-b text-right">Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($posts as $post)
      <tr class="hover:bg-gray-50">
        <td class="py-3 px-4 border-b">{{ $post->title }}</td>
       
        <td class="py-3 px-4 border-b">
          @if($post->is_vip)
            <span class="text-yellow-600 font-semibold">Yes</span>
          @else
            <span class="text-gray-500">No</span>
          @endif
        </td>
        <td class="py-3 px-4 border-b">
          {{ $post->is_vip ? $post->required_points : '-' }}
        </td>
         <td class="py-3 px-4 border-b capitalize">{{ $post->status }}</td>
        <td class="py-3 px-4 border-b">{{ $post->created_at->diffForHumans() }}</td>
        <td class="py-3 px-4 border-b text-right space-x-2">
          <a href="{{ route('posts.edit', $post) }}" class="text-blue-600 hover:underline">Edit</a>
          <form action="{{ route('posts.destroy', $post) }}" method="POST" class="inline">
            @csrf @method('DELETE')
            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete this post?')">Delete</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="mt-4">{{ $posts->links() }}</div>
</div>
@endsection