<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laravel</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
  @vite('resources/css/app.css')
  @vite('resources/js/app.js')
</head>

<body class="antialiased bg-neutral-800 text-white overflow-y-scroll">
  <div class="py-2 px-4">
    @auth
    <a href="{{ url('/dashboard') }}"
      class="font-semibold hover:text-neutral-200 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm">Dashboard</a>
    @else
    <a href="/api/auth/redirect"
      class="font-semibold hover:text-neutral-200 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm">Login
      with spotify</a>
    @endauth
  </div>
</body>

</html>