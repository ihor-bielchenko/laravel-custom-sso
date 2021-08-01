<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->id();
			$table->string('name')->nullable(false)->index();
			$table->string('email')->unique();
			$table->string('avatar')->nullable();
			$table->timestamp('email_verified_at')->nullable();
			$table->integer('tariff_id')->default(0);
			$table->boolean('is_email_notify')->default(false);
			$table->boolean('is_animate')->default(false);
			$table->boolean('is_dark_theme')->default(false);
			$table->string('password');
			$table->string('refresh_token')->nullable();
			$table->string('verify_key')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('users');
	}
}
