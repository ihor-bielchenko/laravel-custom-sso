<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Service;

class CheckProjectIdsByAuth
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
		$authId = $request->input('auth_id');
		$queryIds = $request->input('ids')
			? json_decode($request->input('ids'), true)
			: $request->id > 0
				? [ (int) $request->id ]
				: $request->input('project_id')
					? [ (int) $request->input('project_id') ]
					: [];
		$dataIds = UserProject::whereIn('user_id', $authId)
			->get()
			->pluck('project_id')
			->all();

		foreach ($queryIds as $id) {
			if (!in_array($id, $dataIds)) {
				return response([ 'message' => 'access_error' ])->setStatusCode(403);
			}
		}

		return $next($request);
	}
}
