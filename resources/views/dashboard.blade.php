<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Playlist Generator</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
  @vite('resources/css/app.css')
  @vite('resources/js/app.js')
  @vite('resources/js/htmx.min.js')
</head>

<body class="antialiased">
  <form id="artist-search" hx-get='/api/spotify/artists' hx-target='#artist-results'>
    <label for="query">Artist Search</label>
    <input name="query" type="text">
    <button type="submit">Search</button>
  </form>
  <div id="artist-results" class="max-w-sm md:min-w-min md:max-w-md">
  </div>
  <a href="/api/auth/logout">Logout</a>
</body>

</html>