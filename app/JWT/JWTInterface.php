<?php

namespace App\JWT;

use App\Models\User;

interface JWTInterface
{
	/**
	 * Generating user tokens for user
	 * @param App\Models\User $suer
	 * @return array
	 */
	public function auth(User $user) : array;

	/**
	 * Generating tokens array
	 * @param App\Models\User $user
	 * @return array
	 */
	public function generateTokens(User $user) : array;

	/**
	 * Generating access token string
	 * @return string
	 */
	public function generateAccessToken() : string;

	/**
	 * Generating refresh token string
	 * @return string
	 */
	public function generateRefreshToken() : string;
}