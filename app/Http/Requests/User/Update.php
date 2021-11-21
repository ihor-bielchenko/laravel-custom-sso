<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'email' => 'unique:users|string|max:255',
			'name' => 'string|max:255',
			'password' => 'string|max:255',
			'confirm_password' => 'string|max:255',
			'avatar' => 'string|max:255',
			'tariff_id' => 'numeric|min:1',
			'is_email_notify' => 'boolean',
			'is_animate' => 'boolean',
			'is_dark_theme' => 'boolean'
		];
	}
}
