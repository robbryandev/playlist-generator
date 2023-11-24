<?php

namespace App\Classes;
use App\Classes\SpotifyToken;

class SpotifyHelper
{
  public static $globalToken;
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

  private static function _GetRequest(string $endpoint, string $queryParams) {
    if (SpotifyHelper::$globalToken == null or SpotifyHelper::$globalToken->expired()) {
      SpotifyHelper::$globalToken = SpotifyHelper::GetToken();
    }
    $token = SpotifyHelper::$globalToken;

    $curl = curl_init('https://api.spotify.com/v1' . $endpoint . '?' . $queryParams);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$token->getToken()}", "Content-Type: application/x-www-form-urlencoded"));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
    $result = curl_exec($curl);

    return $result;
  }

  public static function SearchArtist(string $query) {
    $queryParams = [
      'q' => $query,
      'type' => 'artist',
      'limit' => 5
    ];

    $result = SpotifyHelper::_GetRequest("/search", http_build_query($queryParams));
    return $result;
  }

  public static function GetRecommendations(array $artists) {
    $queryParams = 'seed_artists=' . join(',', $artists);
    $result = SpotifyHelper::_GetRequest("/recommendations", $queryParams);
    return $result;
  }
}