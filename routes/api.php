<?php

use App\Models\User;
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
            $code = request('code');
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