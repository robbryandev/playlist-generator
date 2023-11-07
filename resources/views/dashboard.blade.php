<?php
use Illuminate\Support\Facades\Auth;
?>

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
  @vite('resources/js/htmx.min.js')
</head>

<body class="antialiased">
  <p class="text-lg text-center text-blue-600">
    <?php
      echo(Auth::getuser());
    ?>
  </p>
  <a href="/api/auth/logout">Logout</a>
</body>

</html>