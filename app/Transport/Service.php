<?php

namespace App\Transport;

class Service implements ServiceInterface
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var array
	 */
	public $replicas;

	/**
	 * @var array
	 */
	public $routes;

	/**
	 * 
	 */
	public function __construct(array $data)
	{
		$this->name = $data['name'];
		$this->replicas = $data['replicas'];
		$this->routes = [];
	}
}