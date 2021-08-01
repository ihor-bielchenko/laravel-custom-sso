<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckHasJWTRefreshToken
{
	/**
	 * Trying to get refresh token
	 * @param Illuminate\Http\Request $request
	 * @return string|null
	 */
	protected function defineRefreshToken(Request $request)
	{
		return $request->headers->has('refresh_token') ?
			$request->header('refresh_token') : 
			$request->input('refresh_token');
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($refreshToken = $this->defineRefreshToken($request)) {
			return $next($request->merge([ 'refresh_token' => $refreshToken ]));
		}
		return response([ 'message' => 'refresh_token is empty' ])
			->setStatusCode(401);
	}
}
