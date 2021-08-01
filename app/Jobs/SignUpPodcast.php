<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SignUpPodcast implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var App\Models\Base;
	 */
	protected $user;

	/**
	 * @var array
	 */
	protected $jwt;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($user, $jwt)
	{
		$this->user = $user;
		$this->jwt = $jwt;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		
	}
}
