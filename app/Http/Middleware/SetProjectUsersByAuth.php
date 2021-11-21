<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Service;

class SetProjectUsersByAuth
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
		$dataIds = UserProject::whereIn('user_id', $authId)
			->get()
			->pluck('project_id')
			->all();

		foreach ($dataIds as $id) {
			try {
				$userIds = UserProject::whereIn('project_id', $id)
					->get()
					->pluck('user_id')
					->all();
				$ids = array_merge($ids, $userIds);
			}
			catch (\Exception $exception) {
				return response([ 'message' => 'access_error' ])->setStatusCode(403);
			}
		}
		$ids = array_unique($ids);

		return $next($request->merge([ 
			'auth_users' => $ids,
		]));
	}
}
