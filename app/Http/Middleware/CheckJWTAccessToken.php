<?php

namespace App\Http\Middleware;

use Closure;
use App\JWT\JWT;
use Illuminate\Http\Request;

class CheckJWTAccessToken
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
		$transportPassword = $request->input(env('TRANSPORT_PASSWORD_KEY'));
		$isServiceWorkerUserFlag = $transportPassword === env('TRANSPORT_PASSWORD_VALUE');
		$explode = explode('.', $request->input('access_token'));
		$payload = [];
		
		if (!$isServiceWorkerUserFlag) {
			if (!isset($explode[0]) || !isset($explode[1])) {
				return response()
					->json([ 'message' => 'signature is not valid' ])
					->setStatusCode(403);
			}
			$payload = json_decode(base64_decode($explode[1]), true);
		
		
			$jwt = new JWT;
			$signature = $jwt->createSignature(
				trim($explode[0] .'.'. $explode[1]), 
				env('JWT_SECRET_ACCESS_KEY')
			);

			// check token
			if (isset($explode[2]) && $signature !== $explode[2]) {
				return response()
					->json([ 'message' => 'signature is not valid' ])
					->setStatusCode(403);
			}

			// check token expire
			if ($jwt->iatDefine() - $payload['exp'] > $payload['iat']) {
				return response()
					->json([ 'message' => 'token is old' ])
					->setStatusCode(403);
			}
		}
		return $next($request->merge([ 
			'auth_id' => $payload['id'] ?? 1,
			'auth_email' => $payload['email'] ?? 'service@drivedatum.com',
		]));
	}
}
