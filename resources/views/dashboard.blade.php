<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Playlist Generator</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
  @vite('resources/css/app.css')
  @vite('resources/js/app.js')
</head>

<body class="antialiased bg-neutral-800 text-white">
  <div class="py-2 px-4">
    <a class="font-semibold hover:text-neutral-200 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm"
      href="/api/auth/logout">Logout</a>
  </div>
  <div id="app">
    <dashboard-page>
  </div>
</body>

</html>