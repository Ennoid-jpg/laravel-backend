<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Build your API here.
|
*/

// Simple login endpoint for static frontends (e.g. GitHub Pages)
Route::post('/login', [LoginController::class, 'login']);

// Optional: handle CORS preflight for the login route
Route::options('/login', function () {
    $githubOrigin = 'https://YOUR-GITHUB-USERNAME.github.io'; // TODO: set real GitHub Pages origin

    return response()
        ->noContent(204)
        ->header('Access-Control-Allow-Origin', $githubOrigin)
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With')
        ->header('Access-Control-Allow-Credentials', 'true');
});

// Example protected route (kept for reference, still requires Sanctum if used)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


