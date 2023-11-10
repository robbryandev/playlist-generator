<?php
use App\Utils\SpotifyHelper;
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
  <!-- @vite('resources/css/app.css') -->
  @vite('resources/js/htmx.min.js')
</head>

<body class="antialiased">
  <p class="text-lg text-center text-blue-600">
    <?php
      $token = SpotifyHelper::GetToken();
      if (!$token->expired()) {
        $artistData = [
          '6vw3QQUYW5TSxqsEvI28W6',
          '77xhQrX7SvDUgGZoJiFNdn',
          '6xTk3EK5T9UzudENVvu9YB'
        ];
        $recommendResult = SpotifyHelper::GetRecommendations($token, $artistData);
        echo('recommendations: ' . $recommendResult . "<br>");
      }
    ?>
  </p>
  <a href="/api/auth/logout">Logout</a>
</body>

</html>