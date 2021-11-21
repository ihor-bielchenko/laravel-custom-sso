<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
	use Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 
		'unique_name',
		'email', 
		'avatar',
		'email_verified_at',
		'tariff_id',
		'is_email_notify',
		'is_animate',
		'is_dark_theme',
		'password',
		'refresh_token',
		'verify_key',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'id',
		'password', 
		'remember_token',
		'verify_key',
		'refresh_token',
		'created_at',
		'updated_at',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	/**
	 * Columns for search
	 * 
	 * @var array
	 */
	protected $indexes = [
		'name',
		'email',
	];

	/**
	 * encrypt password string and prepare to save
	 * @param string $pass
	 */
	public function setPasswordAttribute($password)
	{
		if (!empty($password)) {
			$this->attributes['password'] = bcrypt($password);
		}
	}

	/**
	 * To delete refresh token from DB
	 * @return boolean
	 */
	public function clearRefreshToken()
	{
		$this->refresh_token = '';
		return $this->save();
	}

	/**
	 * Get user
	 * @param string $name
	 * @return string
	 */
	public static function getByName(string $name) : string
	{
		return User::where('name', $name)->firstOrFail();
	}
}
