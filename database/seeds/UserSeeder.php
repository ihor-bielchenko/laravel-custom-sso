<?php

namespace Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
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
