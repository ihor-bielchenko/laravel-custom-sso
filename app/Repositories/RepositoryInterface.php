<?php

namespace App\Repositories;

use App\Models\Base;
use Illuminate\Database\Eloquent\Builder;

interface RepositoryInterface
{
	public function getOne(int $id, array $credentials = []);

	public function getMany(array $data = [], array $credentials = []) : array;

	public function all(array $credentials = []) : array;

	public function create(array $data, array $credentials = []) : Base;

	public function copy(array $data, array $credentials = []) : array;

	public function update(array $data, int $id, array $credentials = []);

	public function delete(array $ids, array $credentials = []);
}