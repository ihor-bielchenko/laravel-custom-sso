<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Transport\Transport;

class JobSignUp implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var App\Transport\Transport;
	 */
	protected $transport;

	/**
	 * @var App\Models\Base;
	 */
	protected $user;

	/**
	 * @var array
	 */
	protected $jwt;

	/**
	 * The number of times the job may be attempted.
	 *
	 * @var int
	 */
	public $tries = 5;

	/**
	 * The number of seconds the job can run before timing out.
	 *
	 * @var int
	 */
	public $timeout = 3;

	/**
	 * Indicate if the job should be marked as failed on timeout.
	 *
	 * @var bool
	 */
	public $failOnTimeout = true;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($user, $jwt)
	{
		$this->onQueue('users');
		$this->transport = new Transport();
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
		$project = $this->transport->post('PROJECT', 'project', [
			'name' => 'First project',
		]);
		$this->transport->post('PROJECT', 'service', [
			'name' => 'First service',
			'project_id' => $project['data']['id'],
			'template_id' => env('SERVICE_TEMPLATE_BASE'),
			'protocol_id' => env('PROTOCOL_TYPE_HTTP'),
			'subdomain_path' => 'first',
		]);
	}
}
