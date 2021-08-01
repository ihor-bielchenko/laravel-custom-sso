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
			'name' => 'engine',
			'email' => 'service.engine@drivedatum.com',
			'email_verified_at' => now(),
			'password' => bcrypt('engIneUs-e-r'),
		]);
    }
}
