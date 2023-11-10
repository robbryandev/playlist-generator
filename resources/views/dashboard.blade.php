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
        $recommendTest = curl_init();
        $recommendData = [
          'seed_artists' => '6vw3QQUYW5TSxqsEvI28W6,77xhQrX7SvDUgGZoJiFNdn,6xTk3EK5T9UzudENVvu9YB',
        ];
        $queryParams = http_build_query($recommendData);
        curl_setopt($recommendTest, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$token->getToken()}", "Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($recommendTest, CURLOPT_URL, "https://api.spotify.com/v1/recommendations?$queryParams");
        curl_setopt($recommendTest, CURLOPT_RETURNTRANSFER, 1 );
        $recommendResult = curl_exec($recommendTest);
        echo($recommendResult . "<br>");
      }
    ?>
  </p>
  <a href="/api/auth/logout">Logout</a>
</body>

</html>