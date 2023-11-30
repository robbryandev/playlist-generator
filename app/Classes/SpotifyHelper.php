<?php

namespace App\Classes;
use App\Classes\SpotifyToken;

use function PHPUnit\Framework\isEmpty;

class SpotifyHelper
{
  public static $globalToken;

  private static function _GetRequest(string $endpoint, string $queryParams) {
    // Get the token to be used
    if (SpotifyHelper::$globalToken == null or SpotifyHelper::$globalToken->expired()) {
      SpotifyHelper::$globalToken = SpotifyHelper::GetToken();
    }
    $token = SpotifyHelper::$globalToken;

    // Call the Spotify API
    $curl = curl_init('https://api.spotify.com/v1' . $endpoint . '?' . $queryParams);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$token->getToken()}", "Content-Type: application/x-www-form-urlencoded"));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
    $result = curl_exec($curl);

    return $result;
  }

  private static function _BodyRequest(string $method, string $endpoint, array $body, bool $usesToken, bool $json = false, string $customAuth = '') {
    // If $json is true, encode the body as a JSON string
    ob_start();
    $out = fopen('php://stdout', 'w');
    $args = $json ? json_encode($body) : http_build_query($body);

    $postCurl = curl_init();

    curl_setopt($postCurl, CURLOPT_VERBOSE, 1);
    curl_setopt($postCurl, CURLOPT_STDERR, $out);
    curl_setopt($postCurl, CURLOPT_URL, str_starts_with($endpoint, 'https://accounts') ? $endpoint : 'https://api.spotify.com/v1' . $endpoint );
    curl_setopt($postCurl, CURLOPT_CUSTOMREQUEST, $method);

    if ($usesToken) {
      if (SpotifyHelper::$globalToken == null or SpotifyHelper::$globalToken->expired()) {
          SpotifyHelper::$globalToken = SpotifyHelper::GetToken();
      }
      $token = SpotifyHelper::$globalToken;
  
      $authHeader = empty($customAuth) ? "Bearer {$token->getToken()}" : $customAuth;
  
      curl_setopt($postCurl, CURLOPT_HTTPHEADER, array(
          'Authorization: ' . $authHeader,
          "Content-Type: " . ($json ? "application/json" : "application/x-www-form-urlencoded")
      ));
    } else {
      curl_setopt($postCurl, CURLOPT_HTTPHEADER, array("Content-Type: " . ($json ? "application/json" : "application/x-www-form-urlencoded")));
    }

    curl_setopt($postCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($postCurl, CURLOPT_POSTFIELDS, $args );

    $postResult = get_object_vars(json_decode(curl_exec($postCurl)));

    fclose($out);
    $debug = ob_get_clean();

    return $postResult;
}

  public static function GetToken(): SpotifyToken {
    // The endpoint to send our request to
    $tokenBase = 'https://accounts.spotify.com/api/token';

    // The data to send in our request
    $tokenArgs = [
      "grant_type" => "client_credentials",
      "client_id" => $_ENV['SPOTIFY_CLIENT_ID'],
      "client_secret" => $_ENV['SPOTIFY_CLIENT_SECRET']
    ];

    // Send a POST request to the token endpoint
    $tokenResult = SpotifyHelper::_BodyRequest('POST', $tokenBase, $tokenArgs, false);

    // Extract the token and expiration date from the response
    $token = "";
    $expires = 0;

    if (isset($tokenResult['access_token'])) {
      $token = $tokenResult['access_token'];
      $expires = $tokenResult['expires_in'];
    }

    // Return a new SpotifyToken object with the token and expiration date
    return new SpotifyToken($token, $expires);
  }

  public static function GetUserToken(string $accessCode) {
    $tokenBase = 'https://accounts.spotify.com/api/token';

    // Args
    $reqArgs = [
      "code" => $accessCode,
      "redirect_uri" => $_ENV['SPOTIFY_ACCESS_REDIRECT'],
      "grant_type" => "authorization_code",
      "client_id" => $_ENV['SPOTIFY_CLIENT_ID'],
      "client_secret" => $_ENV['SPOTIFY_CLIENT_SECRET']
    ];


    $result = SpotifyHelper::_BodyRequest('POST', $tokenBase, $reqArgs, false);
    return !isset($result['error']) ? new SpotifyToken($result['access_token'], $result['expires_in']) : null;
  }

  public static function SearchArtist(string $query) {
    // Build query parameters array
    $queryParams = [
      'q' => $query,
      'type' => 'artist',
      'limit' => 5
    ];

    // Send the GET request to Spotify
    $result = SpotifyHelper::_GetRequest("/search", http_build_query($queryParams));
    return $result;
  }

  public static function GetRecommendations(array $artists) {
    # Build a comma-separated list of artists
    $queryParams = 'seed_artists=' . join(',', $artists);
    # Make a request to the Spotify API
    $result = SpotifyHelper::_GetRequest("/recommendations", $queryParams);
    # Return the result
    return $result;
  }

  public static function NewPlaylist(string $userId, string $userToken, string $playlistName): string {
    $trackEndpoint = "/users/{$userId}/playlists";
    $trackBody = [
      "name" => $playlistName
    ];

    // POST a new playlist to Spotify
    $bearer = "Bearer {$userToken}";
    $playlistPost = SpotifyHelper::_BodyRequest('POST', $trackEndpoint, $trackBody, true, true, $bearer);
    if (isset($playlistPost['id'])) {
      return $playlistPost['id'];
    }
    return 'Error: ' . $bearer;
  }

  public static function AddToPlaylist(string $userToken, string $playlistId, array $trackIds): bool {
    # Create a list of tracks to add to the playlist
    $trackBase = 'spotify:track:';
    $tracks = $trackBase . join(',' . $trackBase, $trackIds);

    # Add the tracks to the playlist
    $trackEndpoint = "/playlists/{$playlistId}/tracks";
    $trackBody = [
      "uris" => $tracks
    ];

    $addTracksPost = SpotifyHelper::_BodyRequest('PUT', $trackEndpoint . '?uris=' . $trackBody['uris'], [], true, true, "Bearer {$userToken}");
    return isset($addTracksPost['snapshot_id']);
  }
}