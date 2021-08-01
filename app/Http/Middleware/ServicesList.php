<?php

namespace App\Http\Middleware;

use Closure;

class ServicesList
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$filter = $request->input('filter')
			? json_decode($request->input('filter'), true)
			: [];
		$filter['ids'] = array_merge($filter['ids'] ?? [], $request->input('auth_services'));
		$filter['ids'] = array_unique($filter['ids']);

		return $next($request->merge([ 
			'filter' => $filter,
		]));
	}
}
