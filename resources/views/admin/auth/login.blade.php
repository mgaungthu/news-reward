<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">

  <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
    <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Admin Login</h2>

    <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
      @csrf

      <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" class="w-full border-0 border-b-2 border-gray-300 rounded-none focus:border-blue-500 focus:outline-none focus:ring-0 focus:ring-offset-0 py-3" required>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" class="w-full border-0 border-b-2 border-gray-300 rounded-none focus:border-blue-500 focus:outline-none focus:ring-0 focus:ring-offset-0 py-3" required>
      </div>

      @if ($errors->any())
        <div class="text-red-500 text-sm">{{ $errors->first() }}</div>
      @endif

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition">
        Sign In
      </button>
    </form>
  </div>

</body>
</html>