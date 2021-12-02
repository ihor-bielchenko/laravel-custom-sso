<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Transport\Transport;

class RedisReplicaDel extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'replica:del';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		try {
			Redis::select(config('REDIS_DB') ?? env('REDIS_DB'));

			$url = Transport::url(env('APP_PROTOCOL'), env('APP_DOMAIN'), env('APP_REPLICA'), (int) env('APP_PORT'));
			$nowReplicas = Redis::lrange('TRANSPORT_REPLICAS_'. env('APP_SERVICE'), 0, -1);

			Redis::del('TRANSPORT_REPLICAS_'. env('APP_SERVICE'));
			
			foreach ($nowReplicas as $nowReplica) {
				if ($nowReplica !== $url) {
					Redis::lpush('TRANSPORT_REPLICAS_'. env('APP_SERVICE'), $nowReplica);
				}
			}
			return Command::SUCCESS;
		}
		catch (\Exception $exception) {
		}

		return Command::SUCCESS;
	}
}
