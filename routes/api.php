<?php

use App\Models\User;
use App\Classes\SpotifyHelper;
use App\Classes\SpotifyArtist;
use App\Classes\SpotifyTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['web']], function () {
    Route::get('/auth/redirect', function () {
        $tokenBase = 'https://accounts.spotify.com/authorize?';
        $tokenArgs = [
          "response_type=code",
          "scope=playlist-modify-public,playlist-modify-private",
          "client_id={$_ENV['SPOTIFY_CLIENT_ID']}",
          "redirect_uri={$_ENV['SPOTIFY_REDIRECT_URI']}"
        ];

        $callbackUrl = $tokenBase . join('&', $tokenArgs);

        return redirect($callbackUrl);
    });

    Route::get('/auth/callback', function () {
        try {
            $error = request('error');
            if (isset($error)) {
                return redirect('/');
            }

            $requestCode = request('code');

            // $user = User::updateOrCreate([
            //     'spotify_id' => $spotifyUser->getId()
            // ], [
            //     'name' => $spotifyUser->name
            // ]);

            // Auth::login($user);

            // return redirect("/dashboard");
        } catch (\Throwable $th) {
            throw $th;
        }
    });

    Route::get("/auth/check", function () {
        if (Auth::check()) {
            return Auth::getUser();
        } else {
            return "Not authenticated";
        }
    });

    Route::get("/auth/logout", function () {
        Auth::logout();
        return redirect("/");
    });

    Route::get("/artists", function () {
        $result = [];
        $query = request('query');
        $searchString = SpotifyHelper::SearchArtist($query);
        $searchResults = json_decode($searchString, true);

        if (!isset($searchResults['artists']) or $searchResults['artists']['total'] == 0) {
            return "0 results found";
        }

        foreach ($searchResults['artists']['items'] as $artist) {
            $artistImage = count($artist['images']) > 0 ? $artist['images'][0]['url'] : "";
            $newArtist = new SpotifyArtist($artist['id'], $artist['name'], $artistImage);
            array_push($result, $newArtist);
        }

        return count($result) > 0 ? $result : "Error with results";
    });

    Route::get("/playlist/new", function () {
        $result = [];
        $seedArtists = request('artists');
        $recommendations = SpotifyHelper::GetRecommendations(explode(',', $seedArtists));
        $jsonRec = json_decode($recommendations, true);
        foreach ($jsonRec['tracks'] as $track) {
            $trackId = $track['id'];
            $trackName = $track['name'];
            $trackArtist = $track['artists'][0]['name'];
            $trackPreview = isset($track['preview_url']) ? $track['preview_url'] : '';
            $trackImg = count($track['album']['images']) > 0 ? $track['album']['images'][0]['url'] : '';
            $newTrack = new SpotifyTrack($trackId, $trackName, $trackArtist, $trackPreview, $trackImg);
            array_push($result, $newTrack);
        }
        return count($result) > 0 ? $result : "Error with results";
    });

    Route::post("/playlist/save", function (Request $request) {
        $body = $request->getPayload()->all();
        $currentUser = $request->user();
        if (!isset($body['tracks']) or !is_array($body['tracks'])) {
            return "Required body parameter 'tracks' array not found";
        }
        if (!isset($body['name']) or !is_string($body['name'])) {
            return "Required body parameter 'name' string not found";
        }
        $tracks = $body['tracks'];
        $playlistName = $body['name'];
        if ($currentUser) {
            $userId = $currentUser['spotify_id'];
            if (!isset($userId)) {
                return "Failed to get user id";
            }
            $playlistId = SpotifyHelper::NewPlaylist($userId, $playlistName);
            if (!str_starts_with($playlistId, 'Error')) {
                $success = SpotifyHelper::AddToPlaylist($playlistId, $tracks);
                return $success ? "Added playlist {$playlistName}" : "Failed to add playlist {$playlistName}";
            }
            return "Failed to get playlist id, " . $playlistId;
        }
        return "Failed to get user";
    });
});