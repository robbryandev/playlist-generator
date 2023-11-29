<?php

use App\Classes\SpotifyToken;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Classes\SpotifyHelper;
use App\Classes\SpotifyArtist;
use App\Classes\SpotifyTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;
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
// AUTH
    Route::get('/auth/redirect', function () {
        $redirectSpotify = Socialite::driver('spotify')->redirect();
        return $redirectSpotify;
    });

    Route::get('/auth/permission', function () {
        $permissionBase = 'https://accounts.spotify.com/authorize?';
        $permissionArgs = [
            'response_type=code',
            "client_id={$_ENV['SPOTIFY_CLIENT_ID']}",
            'scope=playlist-modify-public,playlist-modify-private',
            "redirect_uri={$_ENV['SPOTIFY_ACCESS_REDIRECT']}"
        ];
        $permissionRedirect = $permissionBase . urldecode(join('&', $permissionArgs));
        return redirect($permissionRedirect);
    });

    Route::get('/auth/permission/callback', function () {
        $error = request('error');
        if (isset($error) or !Auth::check()) {
            return redirect('/');
        }
        $code = request('code');
        $user = Auth::getUser();
        DB::update('update users set spotify_access = ? where spotify_id = ?',[urldecode($code), $user['spotify_id']]);
        return redirect('/dashboard');
    });

    Route::get('/auth/callback', function () {
        try {
            $error = request('error');
            if (isset($error)) {
                return redirect('/');
            }
            $spotifyUser = Socialite::driver("spotify")->user();
            $user = User::updateOrCreate([
                'spotify_id' => $spotifyUser->getId()
            ], [
                'name' => $spotifyUser->name
            ]);

            Auth::login($user);

            return redirect("/dashboard");
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

    Route::get("/auth/check/access", function (Request $request) {
        if (!Auth::check()) {
            return "false";
        }
        $minutes = 60;
        $user = Auth::getUser();
        $access = $user['spotify_access'];
        $hasAccess = isset($access);
        if (!$hasAccess) {
            return 'false';
        }
        $token = null;
        $cached = false;
        $cached = Cookie::has('access_token');
        if ($cached) {
            $tokenCache = Cookie::get('access_token');
            if (!isset($tokenCache)) {
                return 'false';
            }
            $tokenCookie = json_decode($tokenCache, true);
            $token = new SpotifyToken($tokenCookie['token'], $tokenCookie['expires']);
        }
        if (!$cached || isset($token) && $token->expired()) {
            $token = SpotifyHelper::GetUserToken($access);
        }
        if (!isset($token) or $token == null) {
            return 'token false';
        }
        return response('true')->cookie(
            'access_token', json_encode($token), $minutes
        );
    });

    Route::get("/auth/logout", function () {
        Auth::logout();
        return redirect("/");
    });


// SPOTIFY
    Route::get("/artists", function () {
        $result = [];
        $query = request('query');
        $searchString = SpotifyHelper::SearchArtist($query);
        $searchResults = json_decode($searchString, true);

        if (!isset($searchResults['artists']) or $searchResults['artists']['total'] == 0) {
            return json_encode([
                'error' => 'no results found'
            ]);
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
        $userTokenCookie = json_decode(Cookie::get('access_token'), true);
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
            $playlistId = SpotifyHelper::NewPlaylist($userId, $userTokenCookie['token'], $playlistName);
            if (!str_starts_with($playlistId, 'Error')) {
                $success = SpotifyHelper::AddToPlaylist($userTokenCookie['token'], $playlistId, $tracks);
                return $success ? "Added playlist {$playlistName}" : "Failed to add playlist {$playlistName}";
            }
            return "Failed to get playlist id, " . $playlistId;
        }
        return "Failed to get user";
    });
});