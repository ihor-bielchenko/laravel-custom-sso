<?php

namespace App\Http\Middleware;

use Closure;

class CheckHasPasswords
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($request->input('password') && $request->input('confirm_password')) {
			return $next($request);
		}
		return response([ 'message' => 'Password not specified' ])
			->setStatusCode(403);
	}
}
