<?php

namespace App\Utils;
use App\Utils\SpotifyToken;

class SpotifyHelper
{
  public static function GetToken(): SpotifyToken {
    $tokenBase = 'https://accounts.spotify.com/api/token';
    $tokenArgs = [
      "grant_type=client_credentials",
      "client_id={$_ENV['SPOTIFY_CLIENT_ID']}",
      "client_secret={$_ENV['SPOTIFY_CLIENT_SECRET']}"
    ];

    $argString = join('&', $tokenArgs);
    $getToken = curl_init();

    curl_setopt($getToken, CURLOPT_URL, $tokenBase );
    curl_setopt($getToken, CURLOPT_POST, 1 );
    curl_setopt($getToken, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($getToken, CURLOPT_POSTFIELDS, $argString );
    curl_setopt($getToken, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));

    $getTokenResult = get_object_vars(json_decode(curl_exec($getToken)));
    $token = "";
    $expires = 0;

    if (isset($getTokenResult['access_token'])) {
      $token = $getTokenResult['access_token'];
      $expires = $getTokenResult['expires_in'];
    }

    return new SpotifyToken($token, $expires);
  }

  public static function GetRecommendations(SpotifyToken $token, array $artists) {
    $recommended = curl_init();
    $queryParams = 'seed_artists=' . join(',', $artists);
    curl_setopt($recommended, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$token->getToken()}", "Content-Type: application/x-www-form-urlencoded"));
    curl_setopt($recommended, CURLOPT_URL, "https://api.spotify.com/v1/recommendations?$queryParams");
    curl_setopt($recommended, CURLOPT_RETURNTRANSFER, 1 );
    $result = curl_exec($recommended);
    return $result;
  }
}