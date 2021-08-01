<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Range as RequestRange;
use App\Http\Resources\GetOne as ResourceGetOne;
use App\Http\Requests\Ids as RequestIds;
use App\Http\Resources\GetMany as ResourceGetMany;
use App\Http\Resources\Copy as ResourceCopy;
use App\Http\Resources\Delete as ResourceDelete;
use App\Http\Resources\Error as ResourceError;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Get one model
	 * @param int $id
	 * @param Illuminate\Http\Request $request
	 * @return Illuminate\Http\Response
	 */
	public function getOne(int $id, Request $request)
	{
		try {
			$credentials = $request->only(
				'auth_id',
				'auth_email',
				'auth_services'
			);
			$model = $this->repository->getOne($id, $credentials);

			return (new ResourceGetOne($model))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	/**
	 * Get collection
	 * @param Illuminate\Http\Request $request
	 * @return Illuminate\Http\Response
	 */
	public function getMany(RequestRange $request)
	{
		try {
			$credentials = $request->only(
				'auth_id',
				'auth_email',
				'auth_services'
			);
			$data = $this->requestGetMenu($request);
			$many = $this->repository->getMany($data, $credentials);

			return (new ResourceGetMany($many))->response();
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
			$credentials = $request->only(
				'auth_id',
				'auth_email',
				'auth_services'
			);
			$many = $this->repository->all($credentials);

			return (new ResourceGetMany($many))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	/**
	 * Copy model
	 * @param App\Http\Requests\Ids $request
	 * @return Illuminate\Http\Response
	 */
	public function copy(RequestIds $request)
	{
		try {
			$credentials = $request->only(
				'auth_id',
				'auth_email',
				'auth_services'
			);
			$ids = $request->input('ids')
				? json_decode($request->input('ids'), true)
				: [];
			$routes = $this->repository->copy($ids, $credentials);

			return (new ResourceCopy($routes))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	/**
	 * Delete model
	 * @param App\Http\Requests\Ids $request
	 * @return Response
	 */
	public function delete(RequestIds $request)
	{
		try {
			$ids = $request->input('ids');
			$credentials = $request->only(
				'auth_id',
				'auth_email',
				'auth_services'
			);
			$ids = $ids
				? gettype($ids) === 'string'
					? json_decode($ids, true)
					: $ids
				: [];
			$result = $this->repository->delete($ids, $credentials);

			return (new ResourceDelete([ 'result' => $result ]))->response();
		}
		catch (\Exception $exception) {
			return $this->responseError($exception);
		}
	}

	public function requestGetMenu(Request $request) : array
	{
		$filter = $request->input('filter');
		$sort = $request->input('sort')
			? json_decode($request->input('sort'), true)
			: [];
		$data = [
			'filter' => $filter
				? gettype($filter) === 'string'
					? json_decode($filter, true)
					: $filter
				: [],
			'search' => $request->input('search') ?? '',
			'sort' => count($sort) > 0
				? $sort
				: [ 'updated_at' => 'desc' ],
			'limit' => $request->input('limit') ?? 10,
		];

		return $data;
	}

	public function responseError(\Exception $exception)
	{
		$code = $exception->getCode();

		return response([ 'message' => $exception->getMessage() ])
			->setStatusCode(gettype($code) === 'integer'
				? $code === 0
					? 500
					: $code
				: 500);
	}
}
