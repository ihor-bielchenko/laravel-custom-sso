<?php

namespace App\Repositories;

use App\Models\Base;
use Illuminate\Database\Eloquent\Builder;

class Repository implements RepositoryInterface
{
	/**
	 * @var App\Models\Base
	 */
	protected $model;

	/**
	 * @var Illuminate\Database\Eloquent\Builder
	 */
	protected $builder;

	/**
	 * Provides model
	 * @param App\Models\Base $model
	 * @return void
	 */
	public function __construct(Base $model)
	{
		$this->model = $model;
	}

	/** 
	 * To form model
	 * @param int $id
	 * @return App\Models\Base
	 */
	public function model(int $id)
	{
		return $this->model->findOrFail($id);
	}

	/** 
	 * Get item by id
	 * @param int $id
	 * @param array $credentials
	 * @return App\Models\Base
	 * @throws \Exception
	 */
	public function getOne(int $id, array $credentials = [])
	{
		try {
			return $this->model($id);
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 404);
		}
	}

	/**
	 * Get collection of models
	 * @param array $data
	 * @param array $credentials
	 * @return array
	 * @throws \Exception
	 */
	public function getMany(array $data = [], array $credentials = []) : array
	{
		try {
			$this->filter($data['filter']);
			$this->search($data['search']);
			$this->sort($data['sort']);
		}
		catch(\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);	
		}

		return $this->paginate($data, $credentials);
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
			return $this->model->get()->toArray();
		}
		catch(\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);	
		}
	}

	/**
	 * Build data with pagination
	 * @param array $data
	 * @return array
	 * @throws \Exception
	 */
	public function paginate(array $data, array $credentials = []) : array
	{
		try {
			$paginate = $this->builder
				? $this->builder->paginate($data['limit'])
				: $this->model->paginate($data['limit']);
		}
		catch(\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}
		return $paginate->toArray();
	}

	/**
	 * Trying to filter by query array
	 * @param array $query
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	public function filter(array $query = [])
	{
		try {
			foreach ($query as $column => $value) {
				if ($value && $this->model::hasAttribute($column)) {
					$this->builder = $this->builder
						? $this->builder->orWhere($column, $value)
						: $this->model::where($column, $value);
				}
			}
			return $this->builder;
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}
	}

	/**
	 * Trying to search by query array
	 * @param string $query
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	public function search(string $query = '')
	{
		try {
			if ($query) {
				// TODO: if $this->model->indexes === '*'
				if ($this->model->indexes ?? []) {
					$firstColumn = array_shift($this->model->indexes);

					$this->builder = $this->builder
						? $this->builder->where($firstColumn, 'ilike', '%'. $query .'%')
						: $this->model::where($firstColumn, 'ilike', '%'. $query .'%');
				}
				foreach ($this->model->indexes ?? [] as $column) {
					$this->builder = $this->builder->orWhere($column, 'ilike', '%'. $query .'%');
				}
			}
			return $this->builder;
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}
	}

	/**
	 * Trying to sort by query array
	 * @param array $query
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	public function sort(array $query = [])
	{
		try {
			if ($query) {
				$firstColumn = array_keys($query)[0];
				$firstValue = array_shift($query);

				$this->builder = $this->builder
					? $this->builder->orderBy($firstColumn, $firstValue)
					: $this->model::orderBy($firstColumn, $firstValue);
			}

			foreach ($query as $column => $value) {
				$this->builder = $this->builder->orderBy($column, $value);
			}
			return $this->builder;
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}
	}

	/**
	 * Create new item in database
	 * @param array $data
	 * @param array $credentials
	 * @return App\Models\Base
	 * @throws \Exception
	 */
	public function create(array $data, array $credentials = []) : Base
	{
		try {
			return $this->model->create($data);
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}
	}

	/**
	 * Update model
	 * @param array $data
	 * @param int $id
	 * @param array $credentials
	 * @return App\Models\Base
	 * @throws \Exception
	 */
	public function update(array $data, int $id, array $credentials = [])
	{
		$item = $this->getOne($id, $credentials);
		
		if (empty($item)) {
			throw new \Exception('not found', 404);
		}
		try {
			$item->update($data);
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}

		return $item;
	}

	/**
	 * Copy model
	 * @param array $ids
	 * @param int $id
	 * @param array $credentials
	 * @return array
	 * @throws \Exception
	 */
	public function copy(array $ids = [], array $credentials = []) : array
	{
		$output = [];

		foreach ($ids as $id) {
			$item = $this->getOne($id, $credentials);
		
			if (empty($item)) {
				throw new \Exception('not found', 404);
			}
			try {
				$data = $item->toArray();
				unset($data['id']);
				$copy = $this->create($data, $credentials);
				$output[] = $copy;
			}
			catch (\Exception $exception) {
				throw new \Exception($exception->getMessage(), 500);
			}
		}
		return $output;
	}

	/**
	 * @param int $id
	 * @param array $credentials
	 * @return boolean is trashed
	 * @throws \Exception
	 */
	public function delete(array $ids  = [], array $credentials = [])
	{
		foreach ($ids as $key => $id) {
			try {
				$item = $this->getOne((int) $id, $credentials);
			}
			catch (\Exception $exception) {
				throw new \Exception($exception->getMessage(), 500);
			}

			try {
				$this->drop($item);
			}
			catch (\Exception $exception) {
				throw new \Exception($exception->getMessage(), 500);
			}
		}
		return true;
	}

	/**
	 * @param App\Models\Base
	 * @return App\Models\Base
	 */
	public function drop($model)
	{
		try {
			if ($model) {
				$model->delete();
			}
		}
		catch (\Exception $exception) {
			throw new \Exception($exception->getMessage(), 500);
		}

		return $model;
	}

	/**
	 *
	 */
	public function ids(array $ids = [])
	{
		return function ($q) use ($ids) {
			$builder = null;

			foreach ($ids as $id) {
				if (is_int($id) && $id > 0) {
					$builder = $builder
						? $q->orWhere('id', $id)
						: $q->where('id', $id);
				}
			}
			return $builder;
		};
	}
}
