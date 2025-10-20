@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
      @error('slug')
      <p class="text-red-500 text-sm mt-1">* {{ $message }}</p>
    @enderror

  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">Create Post</h1>
    <a href="{{ route('posts.index') }}"
       class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-md">
      ← Back
    </a>
  </div>
  <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <div>
      <label class="block text-gray-700 font-medium">Title</label>
      <input name="title" class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2" required>
      @error('title')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>


    <div>
      <label class="block text-gray-700 font-medium">Body</label>
      <textarea name="body" rows="6" class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none" required></textarea>
      @error('body')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="block text-gray-700 font-medium">Category</label>
      <select name="category_id" class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2" required>
        <option value="">Select Category</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
      </select>
      @error('category_id')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="block text-gray-700 font-medium">Tags</label>
      <input name="tags" placeholder="Enter tags separated by commas" class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">
      @error('tags')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="block text-gray-700 font-medium">Read More URL</label>
      <input name="read_more_url" type="url" placeholder="https://example.com/full-article" class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">
      @error('read_more_url')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="block text-gray-700 font-medium">Status</label>
      <select name="status" class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">
        <option value="draft">Draft</option>
        <option value="published">Published</option>
      </select>
      @error('status')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p       >
      @enderror
    </div>

    <div>
      <label class="block text-gray-700 font-medium">Feature Image</label>
      <input type="file" name="feature_image" accept="image/*" >
      @error('feature_image')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>

    <!-- Reward Links Section -->
    <div x-data="{
      rewards: [
        { title: '', type: '', url: '' }
      ],
      addReward() {
        this.rewards.push({ title: '', type: '', url: '' });
      },
      removeReward(idx) {
        this.rewards.splice(idx, 1);
      }
    }" class="mt-6">
      <h2 class="text-lg font-semibold mb-2">Reward Links</h2>
      <template x-for="(reward, idx) in rewards" :key="idx">
        <div class="mb-4 p-4 border border-gray-200 rounded-md bg-gray-50 relative">
          <div class="mb-2">
            <label class="block text-gray-700 text-sm font-medium mb-1">URL</label>
            <input :name="`rewards[${idx}][url]`"
                   x-model="reward.url"
                   class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2"
                   placeholder="https://example.com/reward">
            @error('rewards.*.url')
              <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
            <input type="hidden" :name="`rewards[${idx}][title]`" value="">
            <input type="hidden" :name="`rewards[${idx}][type]`" value="">
          </div>
          <button type="button"
                  x-show="rewards.length > 1"
                  @click="removeReward(idx)"
                  class="absolute top-2 right-2 text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 rounded">
            Remove
          </button>
        </div>
      </template>
      <button type="button"
              @click="addReward"
              class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md">
        + Add Reward
      </button>
    </div>

    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">
      Save Post
    </button>
  </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/{{ env('TINYMCE_API_KEY') }}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    tinymce.init({
      selector: 'textarea[name="body"]',
      height: 400,
      menubar: false,
      plugins: 'link image lists code table media',
      toolbar:
        'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link image media | code',
      branding: false,
      content_style:
        'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
      setup: function (editor) {
        editor.on('change', function () {
          tinymce.triggerSave();
        });
      }
    });
  });
</script>
@endpush