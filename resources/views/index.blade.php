<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
  <title>Playlist Generator</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="antialiased bg-neutral-800 text-white">
  <div id="app">
    @auth
    <index-page isAuthStr='true' />
    @else
    <index-page isAuthStr='false' />
    @endauth
  </div>
</body>

</html>