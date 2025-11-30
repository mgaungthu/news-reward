<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
  <nav class="bg-blue-600 text-white p-4 flex justify-between">
    <a href="{{ route('admin.dashboard') }}" class="font-bold hover:underline">News Rewards Admin</a>
    <form method="POST" action="{{ route('admin.logout') }}">
      @csrf
      <button type="submit" class="hover:underline">Logout</button>
    </form>
  </nav>

  <main class="container mx-auto mt-6">
    @yield('content')
  </main>
  @stack('scripts')
  
</body>
</html>