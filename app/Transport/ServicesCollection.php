<?php

namespace App\Transport;

class ServicesCollection implements ServicesCollectionInterface
{
	/**
	 * @var ing
	 */
	public $length;

	/**
	 * @var array
	 */
	private $_names;

	/**
	 * 
	 */
	public function __construct()
	{
		$this->length = 0;
		$this->_names = [];
	}

	/**
	 * 
	 */
	public function setItem(Service $service)
	{
		$serviceName = $service->name;
		$this->_names[$serviceName] = $service;
	}

	public function getItem(string $serviceName)
	{
		return isset($this->_names[$serviceName])
			? $this->_names[$serviceName]
			: null;
	}
}
