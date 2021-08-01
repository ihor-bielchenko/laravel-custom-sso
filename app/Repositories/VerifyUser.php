<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use App\Jobs\SignUpPodcast;

trait VerifyUser {
	/**
	 * Verify user after register
	 * @param array $data
	 */
	public function verify(array $data = [])
	{
		try {
			$decode = json_decode(base64_decode($data['signature']), true);
			$user = $this->model
				->where('email', $data['email'])
				->where('verify_key', $data['signature'])
				->firstOrFail();
			$createdAt = (int) $user->created_at->format('U');
			$verifiedAt = (int) microtime(true);
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}

		try {
			if (!$user->email_verified_at && $createdAt >= $verifiedAt - 72000 && $decode['password']) {
				$user->email_verified_at = microtime(true);
				$user->verify_key = '';
				$user->save();

				$jwt = $this->jwt->auth($user, $decode['password']);

				SignUpPodcast::dispatch($user, $jwt)
					->allOnQueue('signup')
					->delay(now()->addSeconds(2));

				return $jwt;
			}
			throw new \Exception('signature is old or account already verified', 401);
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}
	}
}
