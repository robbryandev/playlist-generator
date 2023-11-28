<?php

namespace App\Classes;
use App\Classes\SpotifyToken;

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

  private static function _PostRequest(string $endpoint, array $body, bool $usesToken) {
    # Turn the body array into a string
    $argString = join('&', $body);
    # Initialize a curl object
    $postCurl = curl_init();

    # Set the url to the endpoint
    curl_setopt($postCurl, CURLOPT_URL, str_starts_with($endpoint, 'https://accounts') ? $endpoint : 'https://api.spotify.com/v1' . $endpoint );
    # Set the request type to POST
    curl_setopt($postCurl, CURLOPT_POST, 1 );
    # If the endpoint uses a token, include the token in the request
    if ($usesToken) {
      # If the global token is null or expired, get a new token
      if (SpotifyHelper::$globalToken == null or SpotifyHelper::$globalToken->expired()) {
        SpotifyHelper::$globalToken = SpotifyHelper::GetToken();
      }
      $token = SpotifyHelper::$globalToken;
      # Set the Authorization header to the token
      curl_setopt($postCurl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$token->getToken()}", "Content-Type: application/x-www-form-urlencoded"));
    } else {
      # If the endpoint doesn't use a token, don't include the token
      curl_setopt($postCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
    }
    # Set curl to return the response
    curl_setopt($postCurl, CURLOPT_RETURNTRANSFER, 1 );
    # Set the body of the request
    curl_setopt($postCurl, CURLOPT_POSTFIELDS, $argString );

    # Execute the request and decode the response
    $postResult = get_object_vars(json_decode(curl_exec($postCurl)));
    return $postResult;
  }

  public static function GetToken(): SpotifyToken {
    // The endpoint to send our request to
    $tokenBase = 'https://accounts.spotify.com/api/token';

    // The data to send in our request
    $tokenArgs = [
      "grant_type=client_credentials",
      "client_id={$_ENV['SPOTIFY_CLIENT_ID']}",
      "client_secret={$_ENV['SPOTIFY_CLIENT_SECRET']}"
    ];

    // Send a POST request to the token endpoint
    $tokenResult = SpotifyHelper::_PostRequest($tokenBase, $tokenArgs, false);

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

  public static function GetUserToken(string $accessCode): SpotifyToken|null {
    // Args
    $reqArgs = [
      "code={$accessCode}",
      "redirect_uri={$_ENV['SPOTIFY_ACCESS_REDIRECT']}",
      "grant_type=authorization_code"
    ];

    // Call the Spotify API
    $curl = curl_init('https://accounts.spotify.com/api/token?' . join('&', $reqArgs));
    $encodedString = base64_decode($_ENV['SPOTIFY_CLIENT_ID'] . ':' . $_ENV['SPOTIFY_CLIENT_SECRET']);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      "Authorization: Basic {$encodedString}",
      "Content-Type: application/x-www-form-urlencoded"
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );

    $result = curl_exec($curl);
    $jsonRes = is_string($result) ? json_encode($result) : null;
    return isset($jsonRes) ? new SpotifyToken($jsonRes['access_token'], $jsonRes['expires_in']) : null;
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

  public static function NewPlaylist(string $userId, string $playlistName): string {
    $trackEndpoint = "/users/{$userId}/playlists";
    $trackBody = [
      "name={$playlistName}"
    ];

    // POST a new playlist to Spotify
    $playlistPost = SpotifyHelper::_PostRequest($trackEndpoint, $trackBody, true);
    if (isset($playlistPost['id'])) {
      return $playlistPost['id'];
    }
    return 'Error: ' . json_encode($playlistPost);
  }

  public static function AddToPlaylist(string $playlistId, array $trackIds): bool {
    # Create a list of tracks to add to the playlist
    $trackBase = 'spotify:track:';
    $tracks = $trackBase . join(',' . $trackBase, $trackIds);

    # Add the tracks to the playlist
    $trackEndpoint = "/playlists/{$playlistId}/tracks";
    $trackBody = [
      "uris={$tracks}"
    ];

    $addTracksPost = SpotifyHelper::_PostRequest($trackEndpoint, $trackBody, true);
    return isset($addTracksPost['snapshot_id']);
  }
}