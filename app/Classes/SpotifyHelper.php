<?php

namespace App\Classes;
use App\Classes\SpotifyToken;

class SpotifyHelper
{
  public static $globalToken;

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

  private static function _PostRequest(string $endpoint, array $body, bool $usesToken) {
    $argString = join('&', $body);
    $postCurl = curl_init();

    curl_setopt($postCurl, CURLOPT_URL, str_starts_with($endpoint, 'https://accounts') ? $endpoint : 'https://api.spotify.com/v1' . $endpoint );
    curl_setopt($postCurl, CURLOPT_POST, 1 );
    if ($usesToken) {
      if (SpotifyHelper::$globalToken == null or SpotifyHelper::$globalToken->expired()) {
        SpotifyHelper::$globalToken = SpotifyHelper::GetToken();
      }
      $token = SpotifyHelper::$globalToken;
      curl_setopt($postCurl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$token->getToken()}", "Content-Type: application/x-www-form-urlencoded"));
    } else {
      curl_setopt($postCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
    }
    curl_setopt($postCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($postCurl, CURLOPT_POSTFIELDS, $argString );

    $postResult = get_object_vars(json_decode(curl_exec($postCurl)));
    return $postResult;
  }

  public static function GetToken(): SpotifyToken {
    $tokenBase = 'https://accounts.spotify.com/api/token';
    $tokenArgs = [
      "grant_type=authorization_code",
      "client_id={$_ENV['SPOTIFY_CLIENT_ID']}",
      "client_secret={$_ENV['SPOTIFY_CLIENT_SECRET']}"
    ];

    $tokenResult = SpotifyHelper::_PostRequest($tokenBase, $tokenArgs, false);

    $token = "";
    $expires = 0;

    if (isset($tokenResult['access_token'])) {
      $token = $tokenResult['access_token'];
      $expires = $tokenResult['expires_in'];
    }

    return new SpotifyToken($token, $expires);
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

  public static function NewPlaylist(string $userId, string $playlistName): string {
    $trackEndpoint = "/users/{$userId}/playlists";
    $trackBody = [
      "name={$playlistName}"
    ];

    $playlistPost = SpotifyHelper::_PostRequest($trackEndpoint, $trackBody, true);
    if (isset($playlistPost['id'])) {
      return $playlistPost['id'];
    }
    return 'Error: ' . json_encode($playlistPost);
  }

  public static function AddToPlaylist(string $playlistId, array $trackIds): bool {
    $trackBase = 'spotify:track:';
    $tracks = $trackBase . join(',' . $trackBase, $trackIds);

    $trackEndpoint = "/playlists/{$playlistId}/tracks";
    $trackBody = [
      "uris={$tracks}"
    ];

    $addTracksPost = SpotifyHelper::_PostRequest($trackEndpoint, $trackBody, true);
    return isset($addTracksPost['snapshot_id']);
  }
}