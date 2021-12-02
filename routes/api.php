<?php

use Illuminate\Http\Request;

use App\Http\Middleware\CheckHasPasswords;
use App\Http\Middleware\CheckPasswordsMismatch;
use App\Http\Middleware\CheckHasJWTAccessToken;
use App\Http\Middleware\CheckHasJWTRefreshToken;
use App\Http\Middleware\CheckJWTRefreshToken;
use App\Http\Middleware\CheckJWTAccessToken;
use App\Http\Middleware\CheckSourceIsCore;
use App\Http\Middleware\SetProjectUsersByAuth;
use App\Http\Middleware\CheckProjectIdsByAuth;
use App\Http\Middleware\CheckServiceIdByAuth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', 'User@register')
	->middleware([
		CheckHasPasswords::class,
		CheckPasswordsMismatch::class,
	]);

// verify user account
Route::get('/verify', 'User@verify');

// get tokens by user data
Route::get('/login', 'User@login');

// update tokens by refresh_token
Route::get('/refresh', 'User@refresh')
	->middleware(
		CheckHasJWTAccessToken::class,
		CheckHasJWTRefreshToken::class,
		CheckJWTRefreshToken::class
	);

// recovery user access
Route::post('/recovery', 'User@recovery');

Route::middleware([ 
	CheckHasJWTAccessToken::class,
	CheckJWTAccessToken::class,
])->group(function () {
	Route::get('/user', 'User@oneByAccessToken');
});
