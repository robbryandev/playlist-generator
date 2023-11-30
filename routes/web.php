<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

function AuthenticatedRoute($view, $fallbackRoute) {
    if (Auth::check()) {
        return view($view);
    } else {
        return redirect($fallbackRoute);
    }
}

Route::get('/', function () {
    return view('index');
});

Route::get('/dashboard', function () {
    return AuthenticatedRoute("dashboard", "/");
});

Route::get('/success', function () {
    return AuthenticatedRoute("success", "/");
});