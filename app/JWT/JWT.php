<?php

namespace App\JWT;

use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use App\JWT\JWTHashManager;
use \Illuminate\Foundation\Application;

class JWT {

	/**
	 * The hasher implementation.
	 * @var \Illuminate\Contracts\Hashing\Hasher
	 */
	protected $hasher;

	/**
	 * @param  \Illuminate\Contracts\Hashing\Hasher $hasher
	 * @return void
	 */
	public function __construct()
	{
		$this->hasher = new JWTHashManager(app());
		$this->iatDefine();
	}

	/**
	 * Get timestamp
	 * @return int
	 */
	public function iatDefine() : int
	{
		return ($this->iat = round(microtime(true) * 1000));
	}

	/**
	 * Generating tokens for user
	 * @param App\Models\User $user
	 * @param string $password
	 * @return array
	 */
	public function auth(User $user, string $password = '') : array
	{
		if ($this->hasher->check($password, $user->getAuthPassword())) {
			return $this->generateTokens($user);
		}

		return [];
	}

	/**
	 * Generating tokens array
	 * @param App\Models\User $user
	 * @return array
	 */
	public function generateTokens(User $user) : array
	{
		return [
			'user' => $user->toArray(),
			'access_token' => $this->generateAccessToken($user, env('JWT_ACCESS_TIMEOUT')),
			'refresh_token' => $this->generateRefreshToken($user, env('JWT_REFRESH_TIMEOUT'))
		];
	}

	/**
	 * Creating signature string
	 * @param string $publicString
	 * @param array $secretKey
	 * @return string
	 */
	public function createSignature(string $publicString = '', string $secretKey) : string
	{
		return hash_hmac('sha256', $publicString, $secretKey);
	}

	/**
	 * Creating header hash string
	 * @return string
	 */
	public function createHeader() : string
	{
		return base64_encode(json_encode([ 'alg' => 'HS256', 'typ' => 'JWT' ], JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Creating payload hash string
	 * @param array $payload
	 * @return string
	 */
	public function createPayload(array $payload = []) : string
	{
		return base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Generating access token string
	 * @param User $user
	 * @param int $exp - in milliseconds
	 * @return string
	 */
	public function generateAccessToken(User $user, $exp = 300000) : string
	{
		$publicString = $this->createHeader() .'.'. $this->createPayload([
			'exp' => (int) $exp,
			'id' => $user->id,
			'email' => $user->email,
			'iat' => $this->iat,
		]);

		return $publicString .'.'. $this->createSignature(trim($publicString), env('JWT_SECRET_ACCESS_KEY'));
	}

	/**
	 * Generating refresh token string
	 * @param User $user
	 * @param int $exp - in milliseconds
	 * @return string
	 */
	public function generateRefreshToken(User $user, $exp = 960000) : string
	{
		$publicString = $this->createHeader() .'.'. $this->createPayload([
			'exp' => (int) $exp,
			'id' => $user->id,
			'email' => $user->email,
			'iat' => (int) $this->iat,
		]);

		return $this->createSignature(trim($publicString), env('JWT_SECRET_REFRESH_KEY'));
	}
}
