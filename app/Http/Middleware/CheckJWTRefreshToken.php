<?php

namespace App\Http\Middleware;

use Closure;
use App\JWT\JWT;
use Illuminate\Http\Request;

class CheckJWTRefreshToken
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		$explode = explode('.', $request->input('access_token'));
		$payload = json_decode(base64_decode($explode[1]), true);

		$jwt = new JWT;
		$publicString = $jwt->createHeader() .'.'. $jwt->createPayload(array_merge(
			$payload, [ 'exp' => ((int) env('JWT_REFRESH_TIMEOUT')) ?? 960000 ]
		));

		$signature = $jwt->createSignature(trim($publicString), env('JWT_SECRET_REFRESH_KEY'));

		// check token
		if ($signature !== $request->input('refresh_token')) {
			return response()
				->json([ 'message' => 'signature is not valid' ])
				->setStatusCode(401);
		}

		// check token expire
		if ($jwt->iatDefine() - (((int) env('JWT_REFRESH_TIMEOUT')) ?? 960000) > $payload['iat']) {
			return response()
				->json([ 'message' => 'token is old' ])
				->setStatusCode(401);
		}

		return $next($request->merge([ 'email' => $payload['email'] ]));
	}
}
