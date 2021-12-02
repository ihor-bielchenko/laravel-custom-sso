<?php

namespace App\Transport;

use Illuminate\Support\Facades\Redis;

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
		$this->services = new ServicesCollection();
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
		Redis::select(config('REDIS_DB'));
		$redisKeys = Redis::keys('transport_replicas_*');

		foreach ($redisKeys as $key) {
			$explode = explode('transport_replicas_', $key);
			$name = $explode[count($explode) - 1];

			if (!$this->services[$name]) {
				$replicas = Redis::lrange('transport_replicas_'. $name, 0, -1);

				$this->services->setItem(new Service([
					'name' => $name,
					'port' => $port,
					'protocol' => $protocol,
					'domain' => $domain,
					'replicas' => (Redis::lrange('transport_replicas_'. $name, 0, -1) ?: []),
				]));
			}
		}
	}

	/**
	 * 
	 */
	public function cached(string $serviceName, string $routePath) : array
	{
		try {
			return $this->services->getItem($serviceName)->routes[$routePath];
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
		$service = $this->services->getItem($serviceName);
		$replicas = $service->replicas;
		$currentCeplica = explode(',', env('APP_REPLICA'));
		$responseContent = null;
		$responseData = null;
		$i = 0;

		foreach ($replicas as $replica) {
			if ($replica !== $currentCeplica) {
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
				
				$statusCode = 0;
			
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

				if (!isset($service->routes[$routePath])) {
					$service->routes = [];
				}
				$service->routes[$routePath] = $responseData;
			}
		}
		return $responseData;
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
		return $this->request('POST', $serviceName, $routePath, [
			'body' => array_merge($props, [
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
				'body' => array_merge($props, [
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
				'body' => array_merge($props, [
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
