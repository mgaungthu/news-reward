@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
  <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white text-xl hidden z-50">
      Sending notifications, please wait...
  </div>
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-gray-800">Send Push Notification</h1>
    <a href="{{ route('admin.dashboard') }}"
       class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-md">
      Back
    </a>
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 text-green-700 bg-green-100 border border-green-300 rounded">
      {{ session('success') }}
    </div>
  @elseif(session('error'))
    <div class="mb-4 p-3 text-red-700 bg-red-100 border border-red-300 rounded">
      {{ session('error') }}
    </div>
  @endif

  <form id="notificationForm" action="#" method="POST" class="space-y-6">
    @csrf
    
    <div>
      <label class="block text-gray-700 font-medium mb-2">Select User(s)</label>
      <div class="flex items-center mb-2">
        <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
        <label for="selectAll" class="ml-2 text-gray-700 font-medium cursor-pointer">Select All</label>
      </div>
    <div style="max-height: 400px; overflow-y:auto;" class="border border-gray-300 rounded-md p-3">        @foreach($users as $user)
          <label class="flex items-center py-1">
            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                   class="user-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
            <span class="ml-2 text-gray-700">
              {{ $user->email }}
              @if($user->expo_push_token)
                <span class="text-xs text-green-600">(Token available)</span>
              @else
                <span class="text-xs text-red-500">(No token)</span>
              @endif
            </span>
          </label>
        @endforeach
      </div>
      @error('user_ids')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="block text-gray-700 font-medium">Title</label>
      <input type="text" name="title"
             class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2"
             placeholder="Enter notification title"
             required>
      @error('title')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="block text-gray-700 font-medium">Message</label>
      <textarea name="body"
                rows="5"
                class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2"
                placeholder="Write your notification message"
                required></textarea>
      @error('body')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="block text-gray-700 font-medium">Attach Link (optional)</label>
      <input name="link"
             type="url"
             placeholder="https://example.com"
             class="w-full border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2">
      @error('link')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div class="pt-4">
      <button type="button"
              id="sendBtn"
              class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md w-full">
        Send Notification
      </button>
    </div>
  </form>

  <div id="responseMessage" class="mt-4 text-center hidden"></div>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function() {
  const checkboxes = document.querySelectorAll('.user-checkbox');
  checkboxes.forEach(cb => cb.checked = this.checked);
});

function chunkArray(arr, size) {
    const result = [];
    for (let i = 0; i < arr.length; i += size) {
        result.push(arr.slice(i, i + size));
    }
    return result;
}

document.getElementById('sendBtn').addEventListener('click', async function () {
    const selected = [...document.querySelectorAll('.user-checkbox:checked')]
        .map(cb => cb.value);

    if (selected.length === 0) {
        alert("Please select at least one user.");
        return;
    }

    const title = document.querySelector("input[name='title']").value;
    const body = document.querySelector("textarea[name='body']").value;
    const link = document.querySelector("input[name='link']").value;

    if (!title || !body) {
        alert("Title and Body are required");
        return;
    }

    document.getElementById('loadingOverlay').classList.remove('hidden');

    const chunks = chunkArray(selected, 100);
    const csrf = document.querySelector("input[name='_token']").value;

    for (const [index, chunk] of chunks.entries()) {
         await fetch("{{ route('notifications.send') }}", {
             method: "POST",
            headers: {
                 "X-CSRF-TOKEN": csrf,
                 "Content-Type": "application/json"
             },
            body: JSON.stringify({
                 user_ids: chunk,
                 title: title,
                 body: body,
                 link: link
             })
         });
        console.log(`Chunk ${index + 1} sent`, chunk);
    }

    document.getElementById('loadingOverlay').classList.add('hidden');

    alert("All notifications sent successfully!");
});
</script>
@endsection