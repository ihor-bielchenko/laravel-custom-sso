<?php

namespace App\Http\Controllers;

use App\Models\User as Model;
use App\Repositories\User as Repository;
use App\Http\Requests\User\Member as RequestMember;
use App\Http\Requests\User\Update as RequestUpdate;
use App\Http\Requests\User\Register as RequestRegister;
use App\Http\Requests\User\Login as RequestLogin;
use App\Http\Requests\User\Verify as RequestVerify;
use App\Http\Requests\User\Refresh as RequestRefresh;
use App\Http\Requests\User\Recovery as RequestRecovery;
use App\Http\Resources\Register as ResourceRegister;
use App\Http\Resources\Login as ResourceLogin;
use App\Http\Resources\Logout as ResourceLogout;
use App\Http\Resources\Recovery as ResourceRecovery;
use App\Http\Resources\Refresh as ResourceRefresh;
use App\Http\Resources\Error as ResourceError;
use App\Http\Resources\Update as ResourceUpdate;
use App\Http\Resources\GetOne as ResourceGetOne;
use App\Http\Resources\GetMany as ResourceGetMany;
use Illuminate\Http\Request;

class User extends Controller
{
	/**
	 * @var App\Repositories\User
	 */
	protected $repository;

	/**
	 * Provides repository functionality to the current controller
	 * @param App\Models\User $user
	 * @return void
	 */
	public function __construct(Model $user)
	{
		$this->repository = new Repository($user);
	}

	/**
	 * Get collection
	 * @param Illuminate\Http\Request $request
	 * @return Illuminate\Http\Response
	 */
	public function oneByAccessToken(Request $request)
	{
		try {
			$credentials = $request->only(
				'auth_id',
				'auth_email',
				'auth_services'
			);
			$model = $this->repository->getOne($credentials['auth_id'], $credentials);

			return response($model);
			return (new ResourceGetOne($many))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	/**
	 * Get all collection
	 * @param Illuminate\Http\Request $request
	 * @return Illuminate\Http\Response
	 */
	public function all(Request $request)
	{
		try {
			$filter = $request->input('filter');
			$data = $request->only('auth_users');
			$data['filter'] = $filter
				? gettype($filter) === 'string'
					? json_decode($filter, true)
					: $filter
				: [];
			$many = $this->repository->all($data);

			return (new ResourceGetMany($many))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	/**
	 * Update
	 * @param App\Http\Requests\Service\Update
	 * @param int $id
	 * @return Illuminate\Http\Response
	 */
	public function update(RequestUpdate $request)
	{
		try {
			$id = $request->input('auth_id');
			$data = $request->only(
				'email',
				'name',
				'password',
				'confirm_password',
				'avatar',
				'tariff_id',
				'is_email_notify',
				'is_animate',
				'is_dark_theme'
			);
			$data = $this->repository->update($data, $id);

			return (new ResourceUpdate($data))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	/**
	 * Register user
	 * @param App\Http\Requests\User\Register
	 * @return Illuminate\Http\JsonResponse
	 */
	public function register(RequestRegister $request)
	{
		try {
			$data = $request->only(
				'name',
				'email',
				'password',
				'confirm_password'
			);
			$this->repository->register($data);

			return (new ResourceRegister([]))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	/**
	 * Login user
	 * @param App\Http\Requests\User\Login
	 * @return Illuminate\Http\JsonResponse
	 */
	public function login(RequestLogin $request)
	{
		try {
			$data = $request->only(
				'email',
				'password'
			);
			$data = $this->repository->login($data);

			return (new ResourceRegister($data))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	/**
	 * Verify user after register
	 * @param App\Http\Requests\User\Verify
	 * @return Illuminate\Http\JsonResponse
	 */
	public function verify(RequestVerify $request)
	{
		try {
			$data = $request->only(
				'email',
				'signature'
			);

			if ($data = $this->repository->verify($data)) {
				return redirect(env('FRONT_URL') .'/verify?access_token='. $data['access_token'] .'&refresh_token='. $data['refresh_token']);
			}

			throw new \Exception('verify error', 500);

		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	/**
	 * Refresh tokens
	 * @param App\Http\Requests\User\Refresh
	 * @return Illuminate\Http\JsonResponse
	 */
	public function refresh(RequestRefresh $request)
	{
		try {
			$data = $request->only(
				'access_token',
				'refresh_token',
				'email'
			);

			$data = $this->repository->refresh($data);

			return (new ResourceRefresh($data))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	/**
	 * Recovery user access
	 * @param App\Http\Requests\User\Recovery
	 * @return Illuminate\Http\JsonResponse
	 */
	public function recovery(RequestRecovery $request)
	{
		try {
			$data = $request->only('email');
			$data = $this->repository->recovery($data);

			return (new ResourceRecovery($data))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}
}
