<?php

namespace App\Transport;

class Services implements ServiceInterface
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var int
	 */
	public $port;

	/**
	 * @var string
	 */
	public $protocol;

	/**
	 * @var string
	 */
	public $domain;

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
		$this->port = (int) $data['port'];
		$this->protocol = $data['protocol'];
		$this->domain = $data['domain'];
		$this->replicas = $data['replicas'];
		$this->routes = [];
	}
}