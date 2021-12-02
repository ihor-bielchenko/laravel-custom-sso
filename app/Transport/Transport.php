<?php

namespace App\Transport;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class Transport implements TransportInterface
{
	/**
	 * @var \GuzzleHttp\Client
	 */
	protected $client;

	/**
	 * @var array
	 */
	private $services;

	/**
	 * 
	 */
	public function __construct()
	{
		$this->client = new \GuzzleHttp\Client();
		$this->_createState();
	}

	/**
	 * 
	 */
	static public function url(string $protocol, string $domain, string $subdomain, int $port) : string
	{
		if ($port === 443) {
			$protocol = 'https';
			$port = '';
		}
		else if ($port === 80) {
			$protocol = 'http';
			$port = '';
		}

		return $protocol .'://'. $subdomain .'.'. $domain . ($port ? (':'. $port) : '');
	}

	/**
	 * 
	 */
	private function _createState()
	{
		Redis::select(env('REDIS_DB'));
		$redisKeys = Redis::keys('TRANSPORT_REPLICAS_*');
		$this->services = [];

		foreach ($redisKeys as $key) {
			$explode = explode('TRANSPORT_REPLICAS_', $key);
			$name = $explode[count($explode) - 1];

			if (!isset($this->services[$name])) {
				$this->services[$name] = [
					'name' => $name,
					'replicas' => (Redis::lrange('TRANSPORT_REPLICAS_'. $name, 0, -1) ?: []),
					'routes' => [],
				];
			}
		}
	}

	/**
	 * 
	 */
	public function cached(string $serviceName, string $routePath) : array
	{
		try {
			return isset($this->services[$serviceName]['routes'][$routePath])
				? $this->services[$serviceName]['routes'][$routePath]
				: [];
		}
		catch (\Exception $exception) {
			return [];
		} 
	}

	/**
	 * 
	 */
	public function request(string $method = 'GET', string $serviceName, string $routePath, array $props) : array
	{
		$service = $this->services[$serviceName];
		$replicas = $service['replicas'];
		$currentReplicas = explode(',', env('APP_REPLICA'));
		$currentReplica = Transport::url(env('APP_PROTOCOL'), env('APP_DOMAIN'), $currentReplicas[0], (int) env('APP_PORT'));
		$responseContent = null;
		$responseData = null;
		$path = '';
		$statusCode = 500;
		$i = 0;

		foreach ($replicas as $replica) {		
			if ($replica !== $currentReplica) {
				$requestUrl = $replica .'/api/'. $routePath;
				try {
					$response = $this->client->request($method, $requestUrl, $props);
				}
				catch (\Exception $exception) {
					// throw new \Exception('Can\'t perform transport request', 500);
					if ($i >= 2) {
						break;
					}
					$i++;
					continue;
				}			
				try {
					$responseContent = $response->getBody()->getContents();
				}
				catch (\Exception $exception) {
					// throw new \Exception('Can\'t get getContents result', 500);
					if ($i >= 2) {
						break;
					}
					$i++;
					continue;
				}

				try {
					$statusCode = (int) $response->getStatusCode();
				}
				catch (\Exception $exception) {
					if ($statusCode !== 200
						&& $statusCode !== 201
						&& $statusCode !== 404
						&& $statusCode !== 401
						&& $statusCode !== 403) {
						// throw new \Exception('Can\'t get getStatusCode result', 500);
						if ($i >= 2) {
							break;
						}
						$i++;
						continue;
					}
				}

				try {
					$responseData = (json_decode($responseContent, true))['data'];
					$path = $requestUrl;
				}
				catch (\Exception $exception) {
					if (!$responseData) {
						// throw new \Exception('Can\'t convert data to json', 500);
						if ($i >= 2) {
							break;
						}
						$i++;
						continue;
					}
				}
				if (!isset($service['routes'][$routePath])) {
					$service['routes'] = [];
				}
				$service['routes'][$routePath] = $responseData;
			}
		}
		return [
			'statusCode' => $statusCode,
			'data' => $responseData,
			'url' => $path,
		];
	}

	/**
	 * 
	 */
	public function get(string $serviceName, string $routePath, array $props = []) : array
	{
		return $this->request('GET', $serviceName, $routePath, [
			'query' => array_merge($props, [
				env('TRANSPORT_PASSWORD_KEY') => env('TRANSPORT_PASSWORD_VALUE'),
			]),
		]);
	}

	/**
	 * 
	 */
	public function post(string $serviceName, string $routePath, array $props = []) : array
	{
		// print_r($serviceName);

		return $this->request('POST', $serviceName, $routePath, [
			'form_params' => array_merge($props, [
				env('TRANSPORT_PASSWORD_KEY') => env('TRANSPORT_PASSWORD_VALUE'),
			]),
		]);
	}

	/**
	 * 
	 */
	public function patch(string $serviceName, string $routePath, array $props = []) : array
	{
		return $this->request('PATCH', $serviceName, $routePath, [
				'form_params' => array_merge($props, [
					env('TRANSPORT_PASSWORD_KEY') => env('TRANSPORT_PASSWORD_VALUE'),
				]),
			]);
	}

	/**
	 * 
	 */
	public function put(string $serviceName, string $routePath, array $props = []) : array
	{
		return $this->request('PUT', $serviceName, $routePath, [
				'form_params' => array_merge($props, [
					env('TRANSPORT_PASSWORD_KEY') => env('TRANSPORT_PASSWORD_VALUE'),
				]),
			]);
	}

	/**
	 * 
	 */
	public function delete(string $serviceName, string $routePath, array $props = []) : array
	{
		return $this->request('DELETE', $serviceName, $routePath, [
				'query' => array_merge($props, [
					env('TRANSPORT_PASSWORD_KEY') => env('TRANSPORT_PASSWORD_VALUE'),
				]),
			]);
	}
}
