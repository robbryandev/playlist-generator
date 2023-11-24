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
        $redirectSpotify = Socialite::driver('spotify')->redirect();
        return $redirectSpotify;
    });

    Route::get('/auth/callback', function () {
        try {
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

    Route::get("/auth/logout", function () {
        Auth::logout();
        return redirect("/");
    });
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