<?php

namespace App\Repositories;

use App\Models\User as Model;
use App\Model\UserProject;
use App\JWT\JWT;
use App\Mail\VerifyAfterRegister;
use Illuminate\Support\Facades\Http;

class User extends Repository
{
	use VerifyUser;

	/**
	 * @var App\JWT\JWT
	 */
	protected $jwt;

	/**
	 * Provides user model and JWT
	 * @param App\Models\User $user
	 */
	public function __construct(Model $model)
	{
		$this->model = $model;
		$this->jwt = new JWT;
	}

	/**
	 * Get collection of models
	 * @param array $data
	 * @param array $credentials
	 * @return array
	 * @throws \Exception
	 */
	public function all(array $credentials = []) : array
	{
		try {
			return $this->model
				->whereIn('id', $credentials['auth_users'])
				->take(10)
				->get()
				->pluck('name')
				->all();
		}
		catch(\Exception $exception) {
			throw new \Exception('db error', 500);	
		}
	}

	/**
	 * Add user as member to project
	 * @param array $data
	 * @param array $credentials
	 * @return App\Models\Base
	 * @throws \Exception
	 */
	public function memberAdd(array $data, array $credentials = []) : Base
	{
		$user = null;

		try {
			if ($data['user_id'] > 0) {
				$user = $this->model
					->where('id', $data['user_id'])
					->firstOrFail()
					->toArray();
			}
			else if ($data['email']) {
				$user = $this->register([
					'email' => $data['email'],
					'name' => $data['email'],
					'password' => '',
					'confirm_password' => '',
				]);
			}
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}

		try {
			return UserProject::create([
				'user_id' => $user['id'],
				'project_id' => $data['project_id'],
			]);
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}
	}

	/**
	 * @param int $id
	 * @param array $credentials
	 * @return boolean is trashed
	 * @throws \Exception
	 */
	public function delete(array $ids  = [], array $credentials = [])
	{
		try {
			UserProject::whereIn('id', $ids)->delete();
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}

		return true;
	}

	/**
	 * Register new user
	 * @param array $data
	 * @return array - generated tokens
	 */
	public function register(array $data = []) : array
	{
		try {
			$config = [
				'password' => $data['password'],
				'email' => $data['email'],
			];
			$signature = base64_encode(json_encode($config));
			$data['verify_key'] = $signature;
			$user = $this->model->create($data);

			\Mail::to($data['email'])->send(new VerifyAfterRegister($data));
			return $user->toArray();
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}
	}

	/**
	 * Login user
	 * @param array $data
	 * @return array
	 */
	public function login(array $data = []) : array
	{
		try {
			$user = $this
				->model
				->where('email', $data['email'])
				->whereNotNull('email_verified_at')
				->firstOrFail();
		}
		catch (\Exception $exception) {
			throw new \Exception('user not found '. $data['email'], 401);
		}

		return $this->jwt->auth($user, $data['password']);
	}

	/**
	 * Refresh tokens
	 * @param array $data
	 * @return boolean
	 */
	public function refresh(array $data = []) : array
	{
		try {
			$user = $this->model
				->where('email', $data['email'])
				->firstOrFail();
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 401);
		}

		return $this->jwt->generateTokens($user);
	}

	/**
	 * Trying to recovery user access
	 * @param string $email
	 * @return boolean
	 */
	public function recovery(array $data = []) : array
	{
		return [];
	}
}
