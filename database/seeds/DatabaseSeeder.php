<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
			'name' => 'service',
            'unique_name' => 'user1',
			'email' => 'service@drivedatum.com',
			'email_verified_at' => now(),
			'password' => bcrypt('qewrvv34RE'),
		]);
    }
}
