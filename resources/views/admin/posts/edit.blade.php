@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Edit Post</h1>
    <a href="{{ route('posts.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-md">Back</a>
  </div>

  <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data" class="space-y-6" x-data="{
    rewards: {{ json_encode(old('rewards', $post->rewards->map(fn($r) => ['url' => $r->url, 'title' => $r->title, 'type' => $r->type]) ?? [['url'=>'','title'=>'','type'=>'']])) }},
    addReward() { this.rewards.push({ title: '', type: '', url: '' }); },
    removeReward(idx) { this.rewards.splice(idx, 1); }
  }" @submit="tinymce.triggerSave()">
    @csrf
    @method('PUT')

    <div>
      <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
      <input id="title" name="title" type="text" value="{{ old('title', $post->title) }}" required
        class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">
      @error('title')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="body" class="block text-sm font-medium text-gray-700">Body</label>
      <textarea id="body" name="body" rows="8" required
        class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">{{ old('body', $post->body) }}</textarea>
      @error('body')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
      <select id="category_id" name="category_id"
        class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">
        <option value="">Select Category</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id) == $category->id)>
            {{ $category->name }}
          </option>
        @endforeach
      </select>
      @error('category_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="tags" class="block text-sm font-medium text-gray-700">Tags</label>
      <input id="tags" name="tags" type="text" placeholder="Enter tags separated by commas"
        value="{{ old('tags', $post->tags) }}"
        class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">
      @error('tags')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="read_more_url" class="block text-sm font-medium text-gray-700">Read More URL</label>
      <input id="read_more_url" name="read_more_url" type="url" placeholder="https://example.com/full-article"
        value="{{ old('read_more_url', $post->read_more_url) }}"
        class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">
      @error('read_more_url')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
      <select id="status" name="status"
        class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">
        <option value="draft" @selected($post->status == 'draft')>Draft</option>
        <option value="published" @selected($post->status == 'published')>Published</option>
      </select>
      @error('status')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div x-data="{ showPoints: {{ old('is_vip', (int) $post->is_vip) ? 'true' : 'false' }} }" class="mt-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">VIP Post?</label>
      <input type="hidden" name="is_vip" value="0">
      <label class="inline-flex items-center space-x-2">
        <input type="checkbox" name="is_vip" value="1" x-model="showPoints" {{ old('is_vip', (int) $post->is_vip) ? 'checked' : '' }}>
        <span>Mark as VIP</span>
      </label>

      <template x-if="showPoints">
        <div class="mt-3">
          <label for="required_points" class="block text-sm font-medium text-gray-700">Required Points</label>
          <input id="required_points" type="number" name="required_points" min="1"
                 value="{{ old('required_points', $post->required_points) }}"
                 class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2"
                 required>
          @error('required_points')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>
      </template>

      @error('is_vip')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="vimeo_url" class="block text-sm font-medium text-gray-700">Vimeo Video URL</label>
      <input id="vimeo_url" name="vimeo_url" type="url" placeholder="https://vimeo.com/123456789"
        value="{{ old('vimeo_url', $post->vimeo_url) }}"
        class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">
      @error('vimeo_url')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="feature_image" class="block text-sm font-medium text-gray-700">Feature Image</label>
      <input type="file" name="feature_image" accept="image/*">
      @error('feature_image')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
      @if($post->feature_image)
        <div class="mt-4">
          <img src="{{ asset('storage/' . $post->feature_image) }}" alt="Feature Image" class="w-48 rounded-lg shadow">
        </div>
      @endif
    </div>

    <!-- Reward Links Section -->
    <div>
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Reward Links</h2>
      <template x-for="(reward, idx) in rewards" :key="idx">
        <div class="mb-4 p-4 bg-gray-50 border border-gray-300 rounded-md relative">
          <div>
            <label :for="`reward-url-${idx}`" class="block text-sm font-medium text-gray-700 mb-1">URL</label>
            <input :id="`reward-url-${idx}`" type="url" :name="`rewards[${idx}][url]`" x-model="reward.url" placeholder="https://example.com/reward"
              class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2" >
            @error('rewards.*.url')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <input type="hidden" :name="`rewards[${idx}][title]`" :value="reward.title">
            <input type="hidden" :name="`rewards[${idx}][type]`" :value="reward.type">
          </div>
          <button type="button" x-show="rewards.length > 1" @click="removeReward(idx)"
            class="absolute top-2 right-2 text-red-600 hover:text-red-800 text-xs font-semibold px-2 py-1 rounded">
            Remove
          </button>
        </div>
      </template>
      <button type="button" @click="addReward"
        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md">
        + Add Reward
      </button>
    </div>
    <div>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">
        Update Post
      </button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/{{ env('TINYMCE_API_KEY') }}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  tinymce.init({
    selector: 'textarea#body',
    height: 400,
    menubar: false,
    plugins: 'link image lists code table media',
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link image media | code',
    branding: false,
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
    setup: function (editor) {
      editor.on('change', function () {
        tinymce.triggerSave();
      });
    }
  });
});
</script>
@endpush